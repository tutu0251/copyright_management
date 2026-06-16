import React from 'react';
import { EntityListPage } from '../components/EntityListPage';

export function Owners() {
  return (
    <EntityListPage
      title="Owners"
      subtitle="Rights holders and creators on record"
      endpoint="/owners"
      permissionPrefix="owners"
      entityName="owner"
      fields={[
        { key: 'legalName', label: 'Legal name', type: 'text', required: true, value: (r) => r.legalName || r.legal_name || '' },
        {
          key: 'entityType',
          label: 'Type',
          type: 'select',
          value: (r) => r.entityType || r.entity_type || 'individual',
          options: ['individual', 'organization'].map((v) => ({ value: v, label: v })),
        },
        { key: 'email', label: 'Email', type: 'text' },
      ]}
      columns={[
        { key: 'legal_name', label: 'Legal name' },
        { key: 'entity_type', label: 'Type', badge: true, tone: () => 'neutral' },
        { key: 'email', label: 'Email' },
      ]}
    />
  );
}
