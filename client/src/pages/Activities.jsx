import { EntityListPage } from '../components/EntityListPage';

export function Activities() {
  return (
    <EntityListPage
      title="Activity log"
      endpoint="/activities"
      columns={[
        {
          key: 'time',
          label: 'Time',
          render: (r) => (r.time ? new Date(r.time).toLocaleString() : '—'),
        },
        { key: 'actor', label: 'Actor' },
        { key: 'action', label: 'Action' },
        { key: 'entity', label: 'Entity' },
      ]}
    />
  );
}
