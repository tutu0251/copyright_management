import { useEffect, useMemo, useState } from 'react';
import { api } from '../api';
import { ChartCard, CHART_PALETTE, themedScales, themedLegend } from '../components/ChartCard';
import { StatusBadge } from '../components/StatusBadge';

const SUMMARY_META = {
  works: { label: 'Works', hint: 'Registered assets' },
  licenses: { label: 'Licenses', hint: 'Agreements issued' },
  usage_reports: { label: 'Usage reports', hint: 'Detections logged' },
  cases: { label: 'Cases', hint: 'Enforcement matters' },
};

function downloadCsv(filename, rows, headers) {
  const head = headers.map((h) => `"${h.label}"`).join(',');
  const body = rows
    .map((r) => headers.map((h) => `"${String(h.value(r) ?? '').replace(/"/g, '""')}"`).join(','))
    .join('\n');
  const blob = new Blob(['﻿' + [head, body].join('\n')], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  a.click();
  URL.revokeObjectURL(url);
}

export function Reports() {
  const [summary, setSummary] = useState(null);
  const [works, setWorks] = useState([]);
  const [licenses, setLicenses] = useState([]);
  const [tab, setTab] = useState('works');
  const [error, setError] = useState('');

  useEffect(() => {
    Promise.all([
      api('/reports/summary'),
      api('/reports/works').catch(() => ({ items: [] })),
      api('/reports/licenses').catch(() => ({ items: [] })),
    ])
      .then(([s, w, l]) => {
        setSummary(s);
        setWorks(w.items || []);
        setLicenses(l.items || []);
      })
      .catch((e) => setError(e.message));
  }, []);

  const summaryEntries = summary ? Object.entries(summary) : [];

  // Distribution of works by copyright status, for visualization.
  const statusDist = useMemo(() => {
    const m = {};
    for (const w of works) {
      const k = w.copyrightStatus || 'unknown';
      m[k] = (m[k] || 0) + 1;
    }
    return m;
  }, [works]);

  const worksHeaders = [
    { label: 'Title', value: (r) => r.title },
    { label: 'Type', value: (r) => r.workType },
    { label: 'Creator', value: (r) => r.creator },
    { label: 'Status', value: (r) => r.copyrightStatus },
    { label: 'Risk', value: (r) => r.riskLevel },
  ];
  const licensesHeaders = [
    { label: 'Work', value: (r) => r.work?.title || '' },
    { label: 'Licensee', value: (r) => r.licensee?.name || '' },
    { label: 'Type', value: (r) => r.licenseType },
    { label: 'Status', value: (r) => r.licenseStatus },
    { label: 'Payment', value: (r) => r.paymentStatus },
    { label: 'Fee', value: (r) => Number(r.feeAmount || 0).toFixed(2) },
  ];

  if (error) return <div className="alert alert--danger">{error}</div>;

  return (
    <div className="stack" style={{ gap: '1.5rem' }}>
      <div className="app-page-head" style={{ padding: 0 }}>
        <h1 className="app-page-head__title">Reports</h1>
        <p className="app-page-head__crumb muted">Portfolio analytics and exportable datasets.</p>
      </div>

      <div className="grid grid--stats">
        {summaryEntries.map(([key, value]) => {
          const meta = SUMMARY_META[key] || { label: key.replace(/_/g, ' '), hint: '' };
          return (
            <div key={key} className="ui-kpi">
              <div className="ui-kpi__label">{meta.label}</div>
              <div className="ui-kpi__value">{value}</div>
              <p className="ui-kpi__hint muted">{meta.hint}</p>
            </div>
          );
        })}
      </div>

      <div className="chart-grid">
        <ChartCard
          title="Portfolio overview"
          hint="Record counts across modules"
          deps={[JSON.stringify(summary)]}
          build={(t) => ({
            type: 'bar',
            data: {
              labels: summaryEntries.map(([k]) => (SUMMARY_META[k]?.label || k.replace(/_/g, ' '))),
              datasets: [{
                label: 'Records',
                data: summaryEntries.map(([, v]) => Number(v) || 0),
                backgroundColor: CHART_PALETTE,
                borderRadius: 6,
                maxBarThickness: 72,
              }],
            },
            options: {
              responsive: true, maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: themedScales(t),
            },
          })}
        />
        <ChartCard
          title="Works by copyright status"
          hint="Distribution of registered assets"
          deps={[JSON.stringify(statusDist)]}
          build={(t) => ({
            type: 'doughnut',
            data: {
              labels: Object.keys(statusDist).map((k) => k.replace(/_/g, ' ')),
              datasets: [{
                data: Object.values(statusDist),
                backgroundColor: CHART_PALETTE,
                borderColor: t.surface,
                borderWidth: 2,
              }],
            },
            options: {
              responsive: true, maintainAspectRatio: false, cutout: '62%',
              plugins: { legend: themedLegend(t) },
            },
          })}
        />
      </div>

      <div className="ui-tabs">
        <div className="ui-tabs__list">
          <button type="button" className={`ui-tabs__tab${tab === 'works' ? ' is-active' : ''}`} onClick={() => setTab('works')}>
            Works ({works.length})
          </button>
          <button type="button" className={`ui-tabs__tab${tab === 'licenses' ? ' is-active' : ''}`} onClick={() => setTab('licenses')}>
            Licenses ({licenses.length})
          </button>
        </div>

        {tab === 'works' && (
          <div className="stack">
            <div className="toolbar" style={{ marginBottom: 0 }}>
              <span className="muted">{works.length} works</span>
              <button type="button" className="btn btn--secondary btn--sm" disabled={!works.length}
                onClick={() => downloadCsv('works-report.csv', works, worksHeaders)}>
                Export CSV
              </button>
            </div>
            <div className="ui-table-card">
              <div className="ui-table-scroll">
                <table className="data-table">
                  <thead>
                    <tr><th>Title</th><th>Type</th><th>Creator</th><th>Status</th><th>Risk</th></tr>
                  </thead>
                  <tbody>
                    {works.slice(0, 100).map((w) => (
                      <tr key={w._id}>
                        <td><strong>{w.title}</strong></td>
                        <td>{w.workType || '—'}</td>
                        <td>{w.creator || '—'}</td>
                        <td><StatusBadge value={w.copyrightStatus} /></td>
                        <td><StatusBadge value={w.riskLevel} /></td>
                      </tr>
                    ))}
                    {works.length === 0 && <tr><td colSpan={5} className="muted">No works.</td></tr>}
                  </tbody>
                </table>
              </div>
            </div>
            {works.length > 100 && <p className="muted">Showing first 100 of {works.length}. Export CSV for the full dataset.</p>}
          </div>
        )}

        {tab === 'licenses' && (
          <div className="stack">
            <div className="toolbar" style={{ marginBottom: 0 }}>
              <span className="muted">{licenses.length} licenses</span>
              <button type="button" className="btn btn--secondary btn--sm" disabled={!licenses.length}
                onClick={() => downloadCsv('licenses-report.csv', licenses, licensesHeaders)}>
                Export CSV
              </button>
            </div>
            <div className="ui-table-card">
              <div className="ui-table-scroll">
                <table className="data-table">
                  <thead>
                    <tr><th>Work</th><th>Licensee</th><th>Type</th><th>Status</th><th>Payment</th><th>Fee</th></tr>
                  </thead>
                  <tbody>
                    {licenses.slice(0, 100).map((l) => (
                      <tr key={l._id}>
                        <td><strong>{l.work?.title || '—'}</strong></td>
                        <td>{l.licensee?.name || '—'}</td>
                        <td>{l.licenseType || '—'}</td>
                        <td><StatusBadge value={l.licenseStatus} /></td>
                        <td><StatusBadge value={l.paymentStatus} /></td>
                        <td>${Number(l.feeAmount || 0).toFixed(2)}</td>
                      </tr>
                    ))}
                    {licenses.length === 0 && <tr><td colSpan={6} className="muted">No licenses.</td></tr>}
                  </tbody>
                </table>
              </div>
            </div>
            {licenses.length > 100 && <p className="muted">Showing first 100 of {licenses.length}. Export CSV for the full dataset.</p>}
          </div>
        )}
      </div>
    </div>
  );
}
