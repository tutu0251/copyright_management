import { EntityListPage } from '../components/EntityListPage';

export function Activities() {
  return (
    <EntityListPage
      title="Activity log"
      subtitle="Audit trail of changes across the workspace"
      endpoint="/activities"
      columns={[
        {
          key: 'time',
          label: 'Time',
          render: (r) => (r.time ? new Date(r.time).toLocaleString() : '—'),
          export: (r) => r.time || '',
        },
        { key: 'actor', label: 'Actor' },
        { key: 'action', label: 'Action', badge: true, tone: () => 'neutral' },
        { key: 'entity', label: 'Entity' },
      ]}
    />
  );
}
