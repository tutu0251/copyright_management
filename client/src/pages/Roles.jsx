import { EntityListPage } from '../components/EntityListPage';

export function Roles() {
  return (
    <EntityListPage
      title="Roles & permissions"
      endpoint="/roles"
      columns={[
        { key: 'name', label: 'Role' },
        { key: 'slug', label: 'Slug' },
        { key: 'description', label: 'Description' },
        { key: 'permission_count', label: 'Permissions' },
      ]}
    />
  );
}
