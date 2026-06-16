import React, { useEffect, useRef, useState } from 'react';
import { api, assetUrl, uploadAsset } from '../api';
import { useAuth } from '../context/AuthContext';
import { formatBytes } from '../utils/format';

const formatSize = formatBytes;

function kindOf(type) {
  const t = String(type || '').toLowerCase();
  if (t.indexOf('image/') === 0) return 'image';
  if (t.indexOf('video/') === 0) return 'video';
  if (t.indexOf('audio/') === 0) return 'audio';
  if (t === 'application/pdf') return 'pdf';
  if (t.indexOf('text/') === 0) return 'text';
  return 'other';
}

function Preview({ asset }) {
  const url = assetUrl(asset.id);
  const kind = kindOf(asset.content_type || asset.contentType);
  if (kind === 'image') {
    return <img src={url} alt={asset.filename} style={{ maxWidth: '100%', maxHeight: '60vh', borderRadius: 10, display: 'block', margin: '0 auto' }} />;
  }
  if (kind === 'video') {
    return <video src={url} controls style={{ width: '100%', maxHeight: '60vh', borderRadius: 10, background: '#000' }} />;
  }
  if (kind === 'audio') {
    return <audio src={url} controls style={{ width: '100%' }} />;
  }
  if (kind === 'pdf' || kind === 'text') {
    return <iframe src={url} title={asset.filename} style={{ width: '100%', height: '60vh', border: '1px solid var(--border)', borderRadius: 10, background: '#fff' }} />;
  }
  return (
    <p className="muted" style={{ textAlign: 'center', padding: '1.5rem 0' }}>
      No inline preview for this file type ({asset.content_type || 'unknown'}). Use Download to open it.
    </p>
  );
}

export function AssetManager({ work, onClose }) {
  const { can } = useAuth();
  const canEdit = can('works.update');
  const [assets, setAssets] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [progress, setProgress] = useState(null); // { done, total, name }
  const [preview, setPreview] = useState(null);
  const fileRef = useRef(null);

  const totalSize = assets.reduce((n, a) => n + (Number(a.size) || 0), 0);

  const load = () => {
    setLoading(true);
    api(`/works/${work.id}/assets`)
      .then((data) => setAssets(data.items || []))
      .catch((e) => setError(e.message))
      .finally(() => setLoading(false));
  };

  useEffect(load, [work.id]);

  const onPick = async (e) => {
    const files = Array.from(e.target.files || []);
    if (!files.length) return;
    setBusy(true);
    setError('');
    const failures = [];
    for (let i = 0; i < files.length; i += 1) {
      const file = files[i];
      setProgress({ done: i, total: files.length, name: file.name });
      try {
        await uploadAsset(work.id, file);
      } catch (err) {
        failures.push(`${file.name}: ${err.message || 'failed'}`);
      }
    }
    setProgress(null);
    setBusy(false);
    if (fileRef.current) fileRef.current.value = '';
    if (failures.length) setError(`${failures.length} upload(s) failed — ${failures.join('; ')}`);
    load();
  };

  const remove = async (asset) => {
    if (!window.confirm(`Remove "${asset.filename}"? This cannot be undone.`)) return;
    setBusy(true);
    setError('');
    try {
      await api(`/assets/${asset.id}`, { method: 'DELETE' });
      if (preview && preview.id === asset.id) setPreview(null);
      load();
    } catch (err) {
      setError(err.message || 'Remove failed');
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className="ui-modal" role="dialog" aria-modal="true">
      <div className="ui-modal__backdrop" onClick={onClose} />
      <div className="ui-modal__panel" style={{ width: 'min(820px, 100%)' }}>
        <div className="ui-modal__head">
          <h3 className="ui-modal__title">Assets — {work.title || 'Work'}</h3>
          <button type="button" className="btn btn--ghost btn--sm" onClick={onClose} aria-label="Close">✕</button>
        </div>
        <div className="ui-modal__body" style={{ maxHeight: '74vh', overflow: 'auto' }}>
          {error && <div style={{ marginBottom: '0.75rem', color: 'var(--danger)' }}>{error}</div>}

          {canEdit && (
            <div className="toolbar" style={{ marginBottom: '1rem' }}>
              <div className="toolbar__grow">
                <input
                  ref={fileRef}
                  type="file"
                  multiple
                  onChange={onPick}
                  disabled={busy}
                  style={{ display: 'none' }}
                  id="asset-file-input"
                />
                <label htmlFor="asset-file-input" className="btn btn--primary btn--sm" style={{ cursor: busy ? 'not-allowed' : 'pointer' }}>
                  {busy ? 'Uploading…' : '⬆ Upload files'}
                </label>
                <span className="muted">
                  {progress
                    ? `Uploading ${progress.done + 1} of ${progress.total}: ${progress.name}`
                    : 'Select one or more files — images, documents, video, audio…'}
                </span>
              </div>
            </div>
          )}

          {preview && (
            <div className="card" style={{ marginBottom: '1rem' }}>
              <div className="ui-modal__head" style={{ padding: '0 0 0.65rem', borderBottom: '1px solid var(--border)', marginBottom: '0.75rem' }}>
                <strong>{preview.filename}</strong>
                <button type="button" className="btn btn--ghost btn--sm" onClick={() => setPreview(null)}>Close preview</button>
              </div>
              <Preview asset={preview} />
            </div>
          )}

          {loading ? (
            <p className="muted">Loading assets…</p>
          ) : assets.length === 0 ? (
            <p className="muted">No assets uploaded for this work yet.</p>
          ) : (
            <>
            <p className="muted" style={{ marginBottom: '0.6rem' }}>
              {assets.length} file{assets.length === 1 ? '' : 's'} · {formatSize(totalSize)} total
            </p>
            <div className="ui-table-card">
              <div className="ui-table-scroll">
                <table className="data-table">
                  <thead>
                    <tr>
                      <th>File</th>
                      <th>Type</th>
                      <th>Size</th>
                      <th style={{ textAlign: 'right' }}>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {assets.map((a) => (
                      <tr key={a.id}>
                        <td><strong>{a.filename}</strong></td>
                        <td>{a.content_type || '—'}</td>
                        <td>{formatSize(a.size)}</td>
                        <td>
                          <div className="table-actions" style={{ justifyContent: 'flex-end' }}>
                            <button type="button" className="btn btn--ghost btn--sm" onClick={() => setPreview(a)}>Preview</button>
                            <a className="btn btn--ghost btn--sm" href={assetUrl(a.id, { download: true })} target="_blank" rel="noopener noreferrer">Download</a>
                            {canEdit && (
                              <button type="button" className="btn btn--ghost btn--sm" style={{ color: 'var(--danger)' }} onClick={() => remove(a)} disabled={busy}>Remove</button>
                            )}
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
            </>
          )}
        </div>
        <div className="ui-modal__foot">
          <button type="button" className="btn btn--secondary" onClick={onClose}>Done</button>
        </div>
      </div>
    </div>
  );
}
