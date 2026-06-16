import React, { useState } from 'react';
import { EntityListPage } from '../components/EntityListPage';
import { AssetManager } from '../components/AssetManager';
import { formatBytes } from '../utils/format';

function WorksSummary({ items }) {
  const files = items.reduce((n, r) => n + (Number(r.asset_count) || 0), 0);
  const bytes = items.reduce((n, r) => n + (Number(r.asset_size) || 0), 0);
  const withFiles = items.filter((r) => (Number(r.asset_count) || 0) > 0).length;
  const stat = (label, value) => (
    <div className="ui-kpi" style={{ padding: '0.85rem 1.1rem' }}>
      <div className="ui-kpi__label">{label}</div>
      <div className="ui-kpi__value" style={{ fontSize: '1.35rem' }}>{value}</div>
    </div>
  );
  return (
    <div className="grid grid--stats">
      {stat('Works', items.length)}
      {stat('Works with files', withFiles)}
      {stat('Total files', files)}
      {stat('Total storage', formatBytes(bytes))}
    </div>
  );
}

const WORK_FIELDS = [
  { key: 'title', label: 'Title', type: 'text', required: true },
  {
    key: 'workType',
    label: 'Type',
    type: 'select',
    value: (r) => r.workType || r.type || 'Text',
    options: ['Text', 'Image', 'Video', 'Audio', 'Software', 'Document', 'Other'].map((v) => ({ value: v, label: v })),
  },
  { key: 'creator', label: 'Creator', type: 'text' },
  { key: 'owner', label: 'Owner', type: 'text' },
  {
    key: 'copyrightStatus',
    label: 'Copyright status',
    type: 'select',
    value: (r) => r.copyrightStatus || r.copyright_status || 'draft',
    options: ['draft', 'registered', 'pending', 'expired', 'public_domain'].map((v) => ({ value: v, label: v })),
  },
  {
    key: 'riskLevel',
    label: 'Risk level',
    type: 'select',
    value: (r) => r.riskLevel || r.risk_level || 'Low',
    options: ['Low', 'Medium', 'High'].map((v) => ({ value: v, label: v })),
  },
  { key: 'registeredAt', label: 'Registration date', type: 'date', value: (r) => r.registration_date || r.registeredAt || '' },
  { key: 'description', label: 'Description', type: 'textarea' },
];

export function Works() {
  const [assetsFor, setAssetsFor] = useState(null);
  return (
    <>
      <EntityListPage
        title="Assets (Works)"
        subtitle="Registered creative works and their copyright status"
        endpoint="/works"
        permissionPrefix="works"
        entityName="work"
        fields={WORK_FIELDS}
        summary={(items) => <WorksSummary items={items} />}
        rowActions={(row) => (
          <button type="button" className="btn btn--secondary btn--sm" onClick={() => setAssetsFor(row)}>
            Assets
          </button>
        )}
        columns={[
          { key: 'title', label: 'Title' },
          { key: 'type', label: 'Type', badge: true, tone: () => 'neutral' },
          { key: 'creator', label: 'Creator' },
          { key: 'copyright_status', label: 'Status', badge: true },
          { key: 'risk_level', label: 'Risk', badge: true },
          {
            key: 'asset_count',
            label: 'Files',
            value: (r) => Number(r.asset_count) || 0,
            export: (r) => Number(r.asset_count) || 0,
          },
          {
            key: 'asset_size',
            label: 'Size',
            render: (r) => formatBytes(r.asset_size),
            export: (r) => Number(r.asset_size) || 0,
          },
          {
            key: 'registration_date',
            label: 'Registered',
            render: (r) => (r.registration_date ? new Date(r.registration_date).toLocaleDateString() : '—'),
            export: (r) => r.registration_date || '',
          },
        ]}
      />
      {assetsFor && <AssetManager work={assetsFor} onClose={() => setAssetsFor(null)} />}
    </>
  );
}
