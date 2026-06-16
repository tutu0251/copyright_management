import React from 'react';
import { EntityListPage } from '../components/EntityListPage';

export function Licenses() {
  return (
    <EntityListPage
      title="Licenses"
      subtitle="Agreements granting usage rights to licensees"
      endpoint="/licenses"
      permissionPrefix="licenses"
      entityName="license"
      fields={[
        { key: 'work', label: 'Work', type: 'select', required: true, optionsEndpoint: '/works', optionValue: 'id', optionLabel: 'title', value: (r) => r.work_id || '' },
        { key: 'licensee', label: 'Licensee', type: 'select', required: true, optionsEndpoint: '/licensees', optionValue: 'id', optionLabel: 'name', value: (r) => r.licensee_id || '' },
        {
          key: 'licenseType', label: 'Type', type: 'select', value: (r) => r.license_type || 'non_exclusive',
          options: ['exclusive', 'non_exclusive'].map((v) => ({ value: v, label: v })),
        },
        {
          key: 'licenseStatus', label: 'Status', type: 'select', value: (r) => r.license_status || 'draft',
          options: ['draft', 'active', 'expired', 'terminated'].map((v) => ({ value: v, label: v })),
        },
        {
          key: 'paymentStatus', label: 'Payment', type: 'select', value: (r) => r.payment_status || 'unpaid',
          options: ['unpaid', 'paid', 'partial'].map((v) => ({ value: v, label: v })),
        },
        { key: 'feeAmount', label: 'Fee amount', type: 'number', value: (r) => r.fee_amount || 0 },
        { key: 'territory', label: 'Territory', type: 'text' },
        { key: 'startDate', label: 'Start date', type: 'date', value: (r) => r.start_date || '' },
        { key: 'endDate', label: 'End date', type: 'date', value: (r) => r.end_date || '' },
      ]}
      columns={[
        { key: 'work_title', label: 'Work' },
        { key: 'licensee_name', label: 'Licensee' },
        { key: 'license_type', label: 'Type', badge: true, tone: () => 'neutral' },
        { key: 'license_status', label: 'Status', badge: true },
        {
          key: 'fee_amount',
          label: 'Fee',
          render: (r) => `$${Number(r.fee_amount || 0).toFixed(2)}`,
          export: (r) => Number(r.fee_amount || 0).toFixed(2),
        },
      ]}
    />
  );
}
