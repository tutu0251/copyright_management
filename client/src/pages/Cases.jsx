import { EntityListPage } from '../components/EntityListPage';

export function Cases() {
  return (
    <EntityListPage
      title="Infringement cases"
      endpoint="/cases"
      columns={[
        { key: 'title', label: 'Title' },
        { key: 'work_title', label: 'Work' },
        { key: 'case_status', label: 'Status' },
        { key: 'priority', label: 'Priority' },
      ]}
    />
  );
}
