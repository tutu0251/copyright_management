import React from 'react';
import { EntityListPage } from '../components/EntityListPage';

export function UsageReports() {
  return (
    <EntityListPage
      title="Usage reports"
      subtitle="Detected uses of registered works across sources"
      endpoint="/usage-reports"
      permissionPrefix="usage_reports"
      entityName="usage report"
      fields={[
        { key: 'work', label: 'Work', type: 'select', required: true, optionsEndpoint: '/works', optionValue: 'id', optionLabel: 'title', value: (r) => r.work_id || '' },
        {
          key: 'usageType', label: 'Type', type: 'select', value: (r) => r.usage_type || 'suspected',
          options: ['suspected', 'confirmed', 'authorized', 'dismissed'].map((v) => ({ value: v, label: v })),
        },
        { key: 'detectedSource', label: 'Source', type: 'text', value: (r) => r.detected_source || '' },
        { key: 'detectedAt', label: 'Detected at', type: 'date', value: (r) => r.detected_at || '' },
        { key: 'notes', label: 'Notes', type: 'textarea' },
      ]}
      columns={[
        { key: 'work_title', label: 'Work' },
        { key: 'usage_type', label: 'Type', badge: true },
        { key: 'detected_source', label: 'Source' },
        {
          key: 'detected_at',
          label: 'Detected',
          render: (r) => (r.detected_at ? new Date(r.detected_at).toLocaleString() : '—'),
          export: (r) => r.detected_at || '',
        },
      ]}
    />
  );
}
