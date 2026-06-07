import { EntityListPage } from '../components/EntityListPage';

export function Cases() {
  return (
    <EntityListPage
      title="Infringement cases"
      subtitle="Enforcement matters and their resolution status"
      endpoint="/cases"
      columns={[
        { key: 'title', label: 'Title' },
        { key: 'work_title', label: 'Work' },
        { key: 'case_status', label: 'Status', badge: true },
        { key: 'priority', label: 'Priority', badge: true },
      ]}
    />
  );
}
