import { useEffect, useState } from 'react';
import { api } from '../api';

export function EntityListPage({ title, endpoint, columns, emptyLabel = 'No records yet.' }) {
  const [items, setItems] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const data = await api(endpoint);
        if (!cancelled) setItems(data.items || []);
      } catch (e) {
        if (!cancelled) setError(e.message);
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [endpoint]);

  return (
    <>
      <div className="page-header">
        <h1 className="page-title">{title}</h1>
      </div>
      {error && <div className="alert alert--danger">{error}</div>}
      {loading ? (
        <p className="text-muted">Loading…</p>
      ) : (
        <div className="card table-card">
          <div className="table-wrap">
            <table className="data-table">
              <thead>
                <tr>
                  {columns.map((c) => (
                    <th key={c.key}>{c.label}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {items.length === 0 ? (
                  <tr>
                    <td colSpan={columns.length} className="text-muted">
                      {emptyLabel}
                    </td>
                  </tr>
                ) : (
                  items.map((row, i) => (
                    <tr key={row.id || i}>
                      {columns.map((c) => (
                        <td key={c.key}>{c.render ? c.render(row) : row[c.key] ?? '—'}</td>
                      ))}
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      )}
    </>
  );
}
