import { EntityListPage } from '../components/EntityListPage';

export function Licensees() {
  return (
    <EntityListPage
      title="Licensees"
      endpoint="/licensees"
      columns={[
        { key: 'name', label: 'Name' },
        { key: 'organization', label: 'Organization' },
        { key: 'contact_email', label: 'Email' },
      ]}
    />
  );
}
