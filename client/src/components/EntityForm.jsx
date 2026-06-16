import React, { useEffect, useMemo, useState } from 'react';
import { api } from '../api';

// Coerce an arbitrary stored value into the yyyy-MM-dd a <input type="date"> wants.
function toDateInput(v) {
  if (!v) return '';
  const s = String(v);
  if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
  const d = new Date(s);
  if (isNaN(d.getTime())) return '';
  return d.toISOString().slice(0, 10);
}

function initialValue(field, row) {
  let v;
  if (field.value) v = field.value(row || {});
  else if (row && row[field.key] != null) v = row[field.key];
  else v = field.default != null ? field.default : '';
  if (field.type === 'date') return toDateInput(v);
  return v == null ? '' : v;
}

// Modal create/edit form. `fields` describes the inputs; relational selects can
// declare `optionsEndpoint` to load their choices from another API list.
export function EntityForm({ title, fields, row, onClose, onSubmit }) {
  const [values, setValues] = useState(() => {
    const init = {};
    for (const f of fields) init[f.key] = initialValue(f, row);
    return init;
  });
  const [optionSets, setOptionSets] = useState({});
  const [error, setError] = useState('');
  const [saving, setSaving] = useState(false);

  const remoteFields = useMemo(
    () => fields.filter((f) => f.optionsEndpoint),
    [fields],
  );

  useEffect(() => {
    let cancelled = false;
    (async () => {
      const sets = {};
      for (const f of remoteFields) {
        try {
          const data = await api(f.optionsEndpoint);
          const items = data.items || [];
          sets[f.key] = items.map((it) => ({
            value: it[f.optionValue || 'id'],
            label: String(it[f.optionLabel || 'name'] || it.id),
          }));
        } catch {
          sets[f.key] = [];
        }
      }
      if (!cancelled) setOptionSets(sets);
    })();
    return () => { cancelled = true; };
  }, [remoteFields]);

  const setField = (key, val) => setValues((v) => ({ ...v, [key]: val }));

  const submit = async (e) => {
    e.preventDefault();
    setError('');
    for (const f of fields) {
      if (f.required && (values[f.key] === '' || values[f.key] == null)) {
        setError(`${f.label} is required.`);
        return;
      }
    }
    const payload = {};
    for (const f of fields) {
      let val = values[f.key];
      if (f.type === 'number') val = val === '' ? 0 : Number(val);
      payload[f.key] = val;
    }
    setSaving(true);
    try {
      await onSubmit(payload);
    } catch (err) {
      setError(err.message || 'Save failed');
      setSaving(false);
    }
  };

  return (
    <div className="ui-modal" role="dialog" aria-modal="true">
      <div className="ui-modal__backdrop" onClick={onClose} />
      <div className="ui-modal__panel" style={{ width: 'min(620px, 100%)' }}>
        <form onSubmit={submit}>
          <div className="ui-modal__head">
            <h3 className="ui-modal__title">{title}</h3>
            <button type="button" className="btn btn--ghost btn--sm" onClick={onClose} aria-label="Close">✕</button>
          </div>
          <div className="ui-modal__body" style={{ maxHeight: '70vh', overflow: 'auto' }}>
            {error && <div className="alert alert--danger" style={{ marginBottom: '0.75rem', color: 'var(--danger)' }}>{error}</div>}
            <div className="form-grid">
              {fields.map((f) => {
                const opts = f.optionsEndpoint ? (optionSets[f.key] || []) : f.options;
                const wide = f.type === 'textarea';
                return (
                  <div className="field" key={f.key} style={wide ? { gridColumn: '1 / -1' } : undefined}>
                    <label htmlFor={`f_${f.key}`}>
                      {f.label}{f.required ? ' *' : ''}
                    </label>
                    {f.type === 'textarea' ? (
                      <textarea
                        id={`f_${f.key}`}
                        className="textarea"
                        rows={3}
                        value={values[f.key]}
                        placeholder={f.placeholder || ''}
                        onChange={(e) => setField(f.key, e.target.value)}
                      />
                    ) : opts ? (
                      <select
                        id={`f_${f.key}`}
                        className="select"
                        value={values[f.key]}
                        onChange={(e) => setField(f.key, e.target.value)}
                      >
                        <option value="">{f.placeholder || '— Select —'}</option>
                        {opts.map((o) => (
                          <option key={String(o.value)} value={o.value}>{o.label}</option>
                        ))}
                      </select>
                    ) : (
                      <input
                        id={`f_${f.key}`}
                        className="input"
                        type={f.type === 'number' ? 'number' : f.type === 'date' ? 'date' : 'text'}
                        step={f.type === 'number' ? 'any' : undefined}
                        value={values[f.key]}
                        placeholder={f.placeholder || ''}
                        onChange={(e) => setField(f.key, e.target.value)}
                      />
                    )}
                  </div>
                );
              })}
            </div>
          </div>
          <div className="ui-modal__foot">
            <button type="button" className="btn btn--ghost" onClick={onClose} disabled={saving}>Cancel</button>
            <button type="submit" className="btn btn--primary" disabled={saving}>
              {saving ? 'Saving…' : 'Save'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
