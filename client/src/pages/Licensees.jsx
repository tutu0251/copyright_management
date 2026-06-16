import React from 'react';
import { EntityListPage } from '../components/EntityListPage';

export function Licensees() {
  return (
    <EntityListPage
      title="Licensees"
      subtitle="Organizations and individuals licensing your works"
      endpoint="/licensees"
      permissionPrefix="licensees"
      entityName="licensee"
      fields={[
        { key: 'name', label: 'Name', type: 'text', required: true },
        { key: 'organization', label: 'Organization', type: 'text' },
        { key: 'contactEmail', label: 'Email', type: 'text', value: (r) => r.contactEmail || r.contact_email || '' },
        { key: 'notes', label: 'Notes', type: 'textarea' },
      ]}
      columns={[
        { key: 'name', label: 'Name' },
        { key: 'organization', label: 'Organization' },
        { key: 'contact_email', label: 'Email' },
      ]}
    />
  );
}
