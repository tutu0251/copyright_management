import { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { Chart, registerables } from 'chart.js';
import { api } from '../api';

Chart.register(...registerables);

export function Dashboard() {
  const [data, setData] = useState(null);
  const [error, setError] = useState('');
  const chartRef = useRef(null);
  const canvasRef = useRef(null);

  useEffect(() => {
    api('/dashboard?range=30')
      .then(setData)
      .catch((e) => setError(e.message));
  }, []);

  useEffect(() => {
    if (!data?.chartPayload || !canvasRef.current) return;
    if (chartRef.current) chartRef.current.destroy();
    const cp = data.chartPayload;
    chartRef.current = new Chart(canvasRef.current, {
      type: 'line',
      data: {
        labels: cp.labels,
        datasets: [
          {
            label: 'New works',
            data: cp.registeredWorks,
            borderColor: '#6366f1',
            tension: 0.3,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#cbd5e1' } } },
        scales: {
          x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(148,163,184,0.12)' } },
          y: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(148,163,184,0.12)' } },
        },
      },
    });
    return () => chartRef.current?.destroy();
  }, [data]);

  if (error) return <div className="alert alert--danger">{error}</div>;
  if (!data) return <p className="text-muted">Loading dashboard…</p>;

  return (
    <>
      <div className="page-header">
        <h1 className="page-title">Dashboard</h1>
      </div>
      <div className="kpi-grid">
        {data.stats.map((s) => (
          <div key={s.kpi} className="kpi-card card">
            <div className="kpi-card__label">{s.label}</div>
            <div className="kpi-card__value">{s.value}</div>
          </div>
        ))}
      </div>
      <div className="grid-2" style={{ marginTop: '1.5rem' }}>
        <div className="card">
          <h2 className="card__title">Works registered (12 months)</h2>
          <canvas ref={canvasRef} height="120" />
        </div>
        <div className="card">
          <h2 className="card__title">Recent usage detections</h2>
          <ul className="feed-list">
            {(data.recentUsageDetections || []).map((r) => (
              <li key={r.id}>
                <strong>{r.work_title}</strong> — {r.usage_label} ({r.source || 'unknown'})
              </li>
            ))}
            {(data.recentUsageDetections || []).length === 0 && (
              <li className="text-muted">No recent detections.</li>
            )}
          </ul>
        </div>
      </div>
      <div className="card" style={{ marginTop: '1.5rem' }}>
        <h2 className="card__title">Pinned works</h2>
        <ul className="feed-list">
          {(data.pinnedWorks || []).map((w) => (
            <li key={w.work_id}>
              <Link to={`/works`}>{w.title}</Link> — {w.copyright_status}
            </li>
          ))}
        </ul>
      </div>
    </>
  );
}
