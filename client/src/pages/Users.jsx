import React from 'react';
import { EntityListPage } from '../components/EntityListPage';
import { StatusBadge } from '../components/StatusBadge';

export function Users() {
  return (
    <EntityListPage
      title="Users"
      subtitle="Accounts with workspace access and assigned roles"
      endpoint="/users"
      columns={[
        { key: 'display_name', label: 'Name' },
        { key: 'email', label: 'Email' },
        {
          key: 'roles',
          label: 'Roles',
          render: (r) => (r.roles || []).join(', ') || '—',
          export: (r) => (r.roles || []).join('; '),
        },
        {
          key: 'is_active',
          label: 'Active',
          render: (r) => <StatusBadge value={r.is_active ? 'yes' : 'no'} />,
          export: (r) => (r.is_active ? 'Yes' : 'No'),
        },
      ]}
    />
  );
}
