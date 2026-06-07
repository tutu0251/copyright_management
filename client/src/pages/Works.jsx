import { EntityListPage } from '../components/EntityListPage';

export function Works() {
  return (
    <EntityListPage
      title="Assets (Works)"
      subtitle="Registered creative works and their copyright status"
      endpoint="/works"
      columns={[
        { key: 'title', label: 'Title' },
        { key: 'type', label: 'Type', badge: true, tone: () => 'neutral' },
        { key: 'creator', label: 'Creator' },
        { key: 'copyright_status', label: 'Status', badge: true },
        { key: 'risk_level', label: 'Risk', badge: true },
        {
          key: 'registration_date',
          label: 'Registered',
          render: (r) => (r.registration_date ? new Date(r.registration_date).toLocaleDateString() : '—'),
          export: (r) => r.registration_date || '',
        },
      ]}
    />
  );
}
