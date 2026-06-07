import { EntityListPage } from '../components/EntityListPage';

export function Users() {
  return (
    <EntityListPage
      title="Users"
      endpoint="/users"
      columns={[
        { key: 'display_name', label: 'Name' },
        { key: 'email', label: 'Email' },
        { key: 'roles', label: 'Roles', render: (r) => (r.roles || []).join(', ') },
        {
          key: 'is_active',
          label: 'Active',
          render: (r) => (r.is_active ? 'Yes' : 'No'),
        },
      ]}
    />
  );
}
