import { EntityListPage } from '../components/EntityListPage';

export function Licenses() {
  return (
    <EntityListPage
      title="Licenses"
      subtitle="Agreements granting usage rights to licensees"
      endpoint="/licenses"
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
