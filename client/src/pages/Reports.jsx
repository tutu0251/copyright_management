import { useEffect, useState } from 'react';
import { api } from '../api';

export function Reports() {
  const [summary, setSummary] = useState(null);

  useEffect(() => {
    api('/reports/summary').then(setSummary).catch(() => {});
  }, []);

  return (
    <>
      <div className="page-header">
        <h1 className="page-title">Reports</h1>
      </div>
      <div className="kpi-grid">
        {summary &&
          Object.entries(summary).map(([key, value]) => (
            <div key={key} className="kpi-card card">
              <div className="kpi-card__label">{key.replace(/_/g, ' ')}</div>
              <div className="kpi-card__value">{value}</div>
            </div>
          ))}
      </div>
      <p className="text-muted" style={{ marginTop: '1rem' }}>
        Detailed report views and CSV/PDF export can be added on top of the REST API.
      </p>
    </>
  );
}
