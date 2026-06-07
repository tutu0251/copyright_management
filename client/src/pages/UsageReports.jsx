import { EntityListPage } from '../components/EntityListPage';

export function UsageReports() {
  return (
    <EntityListPage
      title="Usage reports"
      subtitle="Detected uses of registered works across sources"
      endpoint="/usage-reports"
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
