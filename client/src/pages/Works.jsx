import { EntityListPage } from '../components/EntityListPage';

export function Works() {
  return (
    <EntityListPage
      title="Assets (Works)"
      endpoint="/works"
      columns={[
        { key: 'title', label: 'Title' },
        { key: 'type', label: 'Type' },
        { key: 'creator', label: 'Creator' },
        { key: 'copyright_status', label: 'Status' },
        { key: 'risk_level', label: 'Risk' },
        { key: 'registration_date', label: 'Registered' },
      ]}
    />
  );
}
