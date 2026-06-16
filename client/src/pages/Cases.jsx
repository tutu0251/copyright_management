import React from 'react';
import { EntityListPage } from '../components/EntityListPage';

export function Cases() {
  return (
    <EntityListPage
      title="Infringement cases"
      subtitle="Enforcement matters and their resolution status"
      endpoint="/cases"
      permissionPrefix="cases"
      entityName="case"
      fields={[
        { key: 'title', label: 'Title', type: 'text', required: true },
        { key: 'work', label: 'Work', type: 'select', optionsEndpoint: '/works', optionValue: 'id', optionLabel: 'title', value: (r) => r.work_id || '' },
        {
          key: 'caseStatus', label: 'Status', type: 'select', value: (r) => r.case_status || 'open',
          options: ['open', 'investigating', 'resolved', 'closed', 'escalated'].map((v) => ({ value: v, label: v })),
        },
        {
          key: 'priority', label: 'Priority', type: 'select', value: (r) => r.priority || 'medium',
          options: ['low', 'medium', 'high', 'critical'].map((v) => ({ value: v, label: v })),
        },
        { key: 'description', label: 'Description', type: 'textarea' },
      ]}
      columns={[
        { key: 'title', label: 'Title' },
        { key: 'work_title', label: 'Work' },
        { key: 'case_status', label: 'Status', badge: true },
        { key: 'priority', label: 'Priority', badge: true },
      ]}
    />
  );
}
