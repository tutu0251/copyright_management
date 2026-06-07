import { EntityListPage } from '../components/EntityListPage';

export function Owners() {
  return (
    <EntityListPage
      title="Owners"
      endpoint="/owners"
      columns={[
        { key: 'legal_name', label: 'Legal name' },
        { key: 'entity_type', label: 'Type' },
        { key: 'email', label: 'Email' },
      ]}
    />
  );
}
