import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { api } from '../api';
import { useAuth } from '../context/AuthContext';
import { StatusBadge } from './StatusBadge';
import { EntityForm } from './EntityForm';

const PAGE_SIZE = 12;

function cellValue(row, col) {
  return col.value ? col.value(row) : row[col.key];
}

function renderCell(row, col) {
  if (col.render) return col.render(row);
  if (col.badge) return <StatusBadge value={cellValue(row, col)} tone={col.tone?.(row)} />;
  const v = cellValue(row, col);
  return v === null || v === undefined || v === '' ? '—' : v;
}

function toCsv(items, columns) {
  const head = columns.map((c) => `"${c.label}"`).join(',');
  const rows = items.map((row) =>
    columns
      .map((c) => {
        const text = c.export ? c.export(row) : stringify(cellValue(row, c));
        return `"${String(text ?? '').replace(/"/g, '""')}"`;
      })
      .join(','),
  );
  return [head, ...rows].join('\n');
}

function stringify(v) {
  if (v === null || v === undefined) return '';
  if (Array.isArray(v)) return v.join('; ');
  return String(v);
}

function downloadCsv(filename, csv) {
  const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

function TableSkeleton() {
  return (
    <div className="ui-table-card">
      <div style={{ padding: '1rem 1.25rem' }} className="stack">
        {Array.from({ length: 8 }).map((_, i) => (
          <div key={i} className="ui-skeleton" style={{ height: 22, width: `${90 - (i % 3) * 12}%` }} />
        ))}
      </div>
    </div>
  );
}

function singularize(title) {
  if (!title) return 'record';
  const t = title.replace(/\s*\(.*\)\s*$/, '').trim().toLowerCase();
  return t.endsWith('s') ? t.slice(0, -1) : t;
}

export function EntityListPage({
  title,
  subtitle,
  endpoint,
  columns,
  emptyLabel = 'No records yet.',
  searchable = true,
  // CRUD config (optional): when `fields` + `permissionPrefix` are provided the
  // page gains New / Edit / Delete controls gated by the matching permissions.
  fields,
  permissionPrefix,
  entityName,
  // Extra per-row controls, e.g. asset management. (row, { reload }) => node
  rowActions,
  // Optional stat strip rendered under the page head. (items) => node
  summary,
}) {
  const { can } = useAuth();
  const [items, setItems] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);
  const [query, setQuery] = useState('');
  const [sort, setSort] = useState({ key: null, dir: 'asc' });
  const [page, setPage] = useState(1);
  const [editing, setEditing] = useState(null); // { mode: 'create' } | { mode: 'edit', row }

  const noun = entityName || singularize(title);
  const canCreate = !!(fields && permissionPrefix && can(`${permissionPrefix}.create`));
  const canUpdate = !!(fields && permissionPrefix && can(`${permissionPrefix}.update`));
  const canDelete = !!(permissionPrefix && can(`${permissionPrefix}.delete`));
  const hasRowActions = !!(rowActions || canUpdate || canDelete);

  const load = useCallback(async () => {
    setLoading(true);
    try {
      const data = await api(endpoint);
      setItems(data.items || []);
      setError('');
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, [endpoint]);

  useEffect(() => { load(); }, [load]);

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();
    if (!q) return items;
    return items.filter((row) =>
      columns.some((c) => {
        const v = c.render ? null : cellValue(row, c);
        return v != null && stringify(v).toLowerCase().includes(q);
      }) || JSON.stringify(row).toLowerCase().includes(q),
    );
  }, [items, query, columns]);

  const sorted = useMemo(() => {
    if (!sort.key) return filtered;
    const col = columns.find((c) => c.key === sort.key);
    if (!col) return filtered;
    const arr = [...filtered].sort((a, b) => {
      const av = stringify(cellValue(a, col));
      const bv = stringify(cellValue(b, col));
      const n = Number(av) - Number(bv);
      const cmp = !Number.isNaN(n) && av !== '' && bv !== '' ? n : av.localeCompare(bv);
      return sort.dir === 'asc' ? cmp : -cmp;
    });
    return arr;
  }, [filtered, sort, columns]);

  const totalPages = Math.max(1, Math.ceil(sorted.length / PAGE_SIZE));
  const safePage = Math.min(page, totalPages);
  const pageItems = sorted.slice((safePage - 1) * PAGE_SIZE, safePage * PAGE_SIZE);
  const totalCols = columns.length + (hasRowActions ? 1 : 0);

  useEffect(() => { setPage(1); }, [query, sort]);

  const toggleSort = (key) => {
    setSort((s) => (s.key === key ? { key, dir: s.dir === 'asc' ? 'desc' : 'asc' } : { key, dir: 'asc' }));
  };

  const exportCsv = () => {
    const csv = toCsv(sorted, columns);
    downloadCsv(`${(title || 'export').toLowerCase().replace(/\s+/g, '-')}.csv`, csv);
  };

  const submitForm = async (payload) => {
    if (editing && editing.mode === 'edit') {
      await api(`${endpoint}/${editing.row.id}`, { method: 'PUT', body: JSON.stringify(payload) });
    } else {
      await api(endpoint, { method: 'POST', body: JSON.stringify(payload) });
    }
    setEditing(null);
    await load();
  };

  const removeRow = async (row) => {
    const label = row.title || row.name || row.legal_name || row.legalName || 'this record';
    if (!window.confirm(`Delete ${label}? This cannot be undone.`)) return;
    try {
      await api(`${endpoint}/${row.id}`, { method: 'DELETE' });
      await load();
    } catch (e) {
      setError(e.message);
    }
  };

  return (
    <div className="stack" style={{ gap: '1.25rem' }}>
      <div className="app-page-head" style={{ padding: 0 }}>
        <h1 className="app-page-head__title">{title}</h1>
        <p className="app-page-head__crumb muted">
          {subtitle || (loading ? 'Loading…' : `${items.length} record${items.length === 1 ? '' : 's'}`)}
        </p>
      </div>

      {error && <div className="alert alert--danger" style={{ color: 'var(--danger)' }}>{error}</div>}

      {summary && !loading && summary(items)}

      <div className="toolbar">
        <div className="toolbar__grow">
          {searchable && (
            <input
              className="input toolbar__search"
              type="search"
              placeholder={`Search ${title?.toLowerCase() || 'records'}…`}
              value={query}
              onChange={(e) => setQuery(e.target.value)}
            />
          )}
        </div>
        <div className="toolbar__right">
          {!loading && (
            <span className="muted">{sorted.length} of {items.length}</span>
          )}
          <button type="button" className="btn btn--secondary btn--sm" onClick={exportCsv} disabled={loading || !sorted.length}>
            Export CSV
          </button>
          {canCreate && (
            <button type="button" className="btn btn--primary btn--sm" onClick={() => setEditing({ mode: 'create' })}>
              + New {noun}
            </button>
          )}
        </div>
      </div>

      {loading ? (
        <TableSkeleton />
      ) : (
        <>
          <div className="ui-table-card">
            <div className="ui-table-scroll">
              <table className="data-table">
                <thead>
                  <tr>
                    {columns.map((c) => {
                      const sortable = !c.render && c.sortable !== false;
                      const active = sort.key === c.key;
                      return (
                        <th
                          key={c.key}
                          className={sortable ? 'sortable' : undefined}
                          onClick={sortable ? () => toggleSort(c.key) : undefined}
                        >
                          <span className="th-sort">
                            {c.label}
                            {sortable && (
                              <span className="sort-ico">{active ? (sort.dir === 'asc' ? '▲' : '▼') : '↕'}</span>
                            )}
                          </span>
                        </th>
                      );
                    })}
                    {hasRowActions && <th style={{ textAlign: 'right' }}>Actions</th>}
                  </tr>
                </thead>
                <tbody>
                  {pageItems.length === 0 ? (
                    <tr>
                      <td colSpan={totalCols} className="muted">
                        {query ? 'No records match your search.' : emptyLabel}
                      </td>
                    </tr>
                  ) : (
                    pageItems.map((row, i) => (
                      <tr key={row.id || i}>
                        {columns.map((c) => (
                          <td key={c.key}>{renderCell(row, c)}</td>
                        ))}
                        {hasRowActions && (
                          <td>
                            <div className="table-actions" style={{ justifyContent: 'flex-end' }}>
                              {rowActions && rowActions(row, { reload: load })}
                              {canUpdate && (
                                <button type="button" className="btn btn--ghost btn--sm" onClick={() => setEditing({ mode: 'edit', row })}>Edit</button>
                              )}
                              {canDelete && (
                                <button type="button" className="btn btn--ghost btn--sm" style={{ color: 'var(--danger)' }} onClick={() => removeRow(row)}>Delete</button>
                              )}
                            </div>
                          </td>
                        )}
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>

          {totalPages > 1 && (
            <div className="pager">
              <span className="pager__meta">
                Page {safePage} of {totalPages}
              </span>
              <div className="pager__buttons">
                <button type="button" className="btn btn--ghost btn--sm" disabled={safePage <= 1} onClick={() => setPage((p) => p - 1)}>
                  Previous
                </button>
                <button type="button" className="btn btn--ghost btn--sm" disabled={safePage >= totalPages} onClick={() => setPage((p) => p + 1)}>
                  Next
                </button>
              </div>
            </div>
          )}
        </>
      )}

      {editing && (
        <EntityForm
          title={editing.mode === 'edit' ? `Edit ${noun}` : `New ${noun}`}
          fields={fields}
          row={editing.mode === 'edit' ? editing.row : null}
          onClose={() => setEditing(null)}
          onSubmit={submitForm}
        />
      )}
    </div>
  );
}
