import { EntityListPage } from '../components/EntityListPage';

export function Licenses() {
  return (
    <EntityListPage
      title="Licenses"
      endpoint="/licenses"
      columns={[
        { key: 'work_title', label: 'Work' },
        { key: 'licensee_name', label: 'Licensee' },
        { key: 'license_type', label: 'Type' },
        { key: 'license_status', label: 'Status' },
        { key: 'fee_amount', label: 'Fee', render: (r) => `$${Number(r.fee_amount || 0).toFixed(2)}` },
      ]}
    />
  );
}
