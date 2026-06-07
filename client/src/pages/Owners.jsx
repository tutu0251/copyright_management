import { EntityListPage } from '../components/EntityListPage';

export function Owners() {
  return (
    <EntityListPage
      title="Owners"
      subtitle="Rights holders and creators on record"
      endpoint="/owners"
      columns={[
        { key: 'legal_name', label: 'Legal name' },
        { key: 'entity_type', label: 'Type', badge: true, tone: () => 'neutral' },
        { key: 'email', label: 'Email' },
      ]}
    />
  );
}
