import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../api';
import { ChartCard, CHART_PALETTE, themedScales, themedLegend } from '../components/ChartCard';
import { StatusBadge } from '../components/StatusBadge';

// Curated KPIs surfaced as hero cards, with a contextual hint per metric.
const KPI_HINTS = {
  works: 'Total registered assets',
  owners: 'Rights holders on record',
  lic_active: 'Currently in force',
  lic_exp: 'Renewals needed soon',
  lic_rev: 'Collected from paid fees',
  lic_unpaid: 'Outstanding balance',
  usage_total: 'Detections logged',
  usage_infringement: 'Flagged as infringing',
  cases_open: 'Awaiting resolution',
  cases_resolved: 'Closed successfully',
};
const HERO_KPIS = ['works', 'lic_active', 'lic_rev', 'usage_total', 'cases_open', 'lic_exp'];

const num = (v) => Number(String(v ?? '').replace(/[^0-9.-]/g, '')) || 0;

function relTime(iso) {
  if (!iso) return '';
  const diff = (Date.now() - new Date(iso).getTime()) / 1000;
  if (diff < 60) return 'just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  return `${Math.floor(diff / 86400)}d ago`;
}

function DashboardSkeleton() {
  return (
    <div className="stack">
      <div className="grid grid--stats">
        {Array.from({ length: 6 }).map((_, i) => (
          <div key={i} className="ui-skeleton" style={{ height: 104 }} />
        ))}
      </div>
      <div className="chart-grid">
        {Array.from({ length: 4 }).map((_, i) => (
          <div key={i} className="ui-skeleton" style={{ height: 300 }} />
        ))}
      </div>
    </div>
  );
}

export function Dashboard() {
  const [data, setData] = useState(null);
  const [error, setError] = useState('');
  const [range, setRange] = useState(30);
  const [workType, setWorkType] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    const qs = new URLSearchParams({ range: String(range) });
    if (workType) qs.set('work_type', workType);
    api(`/dashboard?${qs.toString()}`)
      .then((d) => !cancelled && setData(d))
      .catch((e) => !cancelled && setError(e.message))
      .finally(() => !cancelled && setLoading(false));
    return () => { cancelled = true; };
  }, [range, workType]);

  if (error) return <div className="alert alert--danger">{error}</div>;
  if (loading && !data) return <DashboardSkeleton />;
  if (!data) return null;

  const stat = (kpi) => data.stats.find((s) => s.kpi === kpi);
  const cp = data.chartPayload || {};
  const heroes = HERO_KPIS.map(stat).filter(Boolean);

  const revenue = num(stat('lic_rev')?.value);
  const unpaid = num(stat('lic_unpaid')?.value);
  const usageTotal = num(stat('usage_total')?.value);
  const usageInfr = num(stat('usage_infringement')?.value);
  const usageSuspected = num(data.extraStats?.find((s) => s.kpi === 'usage_suspected')?.value);
  const usageOther = Math.max(0, usageTotal - usageInfr - usageSuspected);

  return (
    <div className="stack" style={{ gap: '1.5rem' }}>
      <div className="app-page-head" style={{ padding: 0 }}>
        <div className="toolbar" style={{ marginBottom: 0 }}>
          <div>
            <h1 className="app-page-head__title">Dashboard</h1>
            <p className="app-page-head__crumb muted">
              Portfolio health across assets, licensing, usage and enforcement.
            </p>
          </div>
          <div className="toolbar__right">
            {data.dashboardWorkTypes?.length > 0 && (
              <select className="select" value={workType} onChange={(e) => setWorkType(e.target.value)} aria-label="Filter by work type">
                <option value="">All asset types</option>
                {data.dashboardWorkTypes.map((t) => (
                  <option key={t} value={t}>{t}</option>
                ))}
              </select>
            )}
            <select className="select" value={range} onChange={(e) => setRange(Number(e.target.value))} aria-label="Detection range">
              <option value={7}>Last 7 days</option>
              <option value={30}>Last 30 days</option>
              <option value={90}>Last 90 days</option>
            </select>
          </div>
        </div>
      </div>

      <div className="grid grid--stats">
        {heroes.map((s) => (
          <div key={s.kpi} className="ui-kpi">
            <div className="ui-kpi__label">{s.label}</div>
            <div className="ui-kpi__value">{s.value}</div>
            <p className="ui-kpi__hint muted">{KPI_HINTS[s.kpi] || ''}</p>
          </div>
        ))}
      </div>

      <div className="chart-grid">
        <ChartCard
          title="New works registered"
          hint="Monthly registrations over the last 12 months"
          deps={[JSON.stringify(cp.registeredWorks)]}
          build={(t) => ({
            type: 'line',
            data: {
              labels: cp.labels || [],
              datasets: [{
                label: 'New works',
                data: cp.registeredWorks || [],
                borderColor: CHART_PALETTE[0],
                backgroundColor: 'rgba(99,102,241,0.15)',
                fill: true,
                tension: 0.35,
                pointRadius: 2,
                pointHoverRadius: 5,
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
          title="Cases by status"
          hint="Distribution of infringement cases"
          deps={[JSON.stringify(cp.caseStatusValues)]}
          build={(t) => ({
            type: 'doughnut',
            data: {
              labels: (cp.caseStatusLabels || []).map((l) => String(l || '—').replace(/_/g, ' ')),
              datasets: [{
                data: cp.caseStatusValues || [],
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

        <ChartCard
          title="License revenue"
          hint="Collected vs. outstanding fees"
          deps={[revenue, unpaid]}
          build={(t) => ({
            type: 'doughnut',
            data: {
              labels: ['Collected', 'Outstanding'],
              datasets: [{
                data: [revenue, unpaid],
                backgroundColor: [CHART_PALETTE[2], CHART_PALETTE[4]],
                borderColor: t.surface,
                borderWidth: 2,
              }],
            },
            options: {
              responsive: true, maintainAspectRatio: false, cutout: '62%',
              plugins: {
                legend: themedLegend(t),
                tooltip: { callbacks: { label: (c) => `${c.label}: $${Number(c.raw).toLocaleString()}` } },
              },
            },
          })}
        />

        <ChartCard
          title="Usage detections by type"
          hint="How detected usages break down"
          deps={[usageInfr, usageSuspected, usageOther]}
          build={(t) => ({
            type: 'bar',
            data: {
              labels: ['Infringement', 'Suspected', 'Other'],
              datasets: [{
                label: 'Detections',
                data: [usageInfr, usageSuspected, usageOther],
                backgroundColor: [CHART_PALETTE[4], CHART_PALETTE[3], CHART_PALETTE[1]],
                borderRadius: 6,
                maxBarThickness: 64,
              }],
            },
            options: {
              responsive: true, maintainAspectRatio: false,
              plugins: { legend: { display: false } },
              scales: themedScales(t),
            },
          })}
        />
      </div>

      <div className="grid grid--dashboard-mid">
        <div className="card">
          <h2 className="card__title">Recent usage detections</h2>
          <div className="ui-table-scroll">
            <table className="data-table">
              <thead>
                <tr>
                  <th>Work</th><th>Type</th><th>Source</th><th>Detected</th>
                </tr>
              </thead>
              <tbody>
                {(data.recentUsageDetections || []).map((r) => (
                  <tr key={r.id}>
                    <td><strong>{r.work_title}</strong></td>
                    <td><StatusBadge value={r.usage_label} /></td>
                    <td>{r.source || '—'}</td>
                    <td className="muted">{relTime(r.detected_at)}</td>
                  </tr>
                ))}
                {(data.recentUsageDetections || []).length === 0 && (
                  <tr><td colSpan={4} className="muted">No detections in this range.</td></tr>
                )}
              </tbody>
            </table>
          </div>
        </div>

        <div className="card">
          <h2 className="card__title">Activity</h2>
          <div className="activity-feed">
            {(data.activity || []).slice(0, 12).map((a, i) => (
              <div className="activity-item" key={i}>
                <span className={`activity-item__dot activity-item__dot--${a.type || 'audit'}`} />
                <div className="activity-item__meta">
                  <div className="activity-item__time">{relTime(a.time)} · {a.actor}</div>
                  <div className="activity-item__text">
                    <strong>{a.action}</strong> {a.entity !== '—' ? `· ${a.entity}` : ''}
                  </div>
                </div>
              </div>
            ))}
            {(data.activity || []).length === 0 && <p className="muted">No recent activity.</p>}
          </div>
        </div>
      </div>

      <div className="card">
        <h2 className="card__title">Pinned works</h2>
        {(data.pinnedWorks || []).length === 0 ? (
          <p className="muted">No pinned works.</p>
        ) : (
          <div className="ui-table-scroll">
            <table className="data-table">
              <thead><tr><th>Title</th><th>Status</th><th></th></tr></thead>
              <tbody>
                {data.pinnedWorks.map((w) => (
                  <tr key={w.work_id}>
                    <td><strong>{w.title}</strong></td>
                    <td><StatusBadge value={w.copyright_status} /></td>
                    <td style={{ textAlign: 'right' }}>
                      <Link className="btn btn--ghost btn--sm" to="/works">View</Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
