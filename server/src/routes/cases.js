import { createCrudRoutes } from './crudFactory.js';
import { InfringementCase } from '../models/Case.js';
import { Work } from '../models/Work.js';

export default createCrudRoutes({
  Model: InfringementCase,
  viewPerm: 'cases.view',
  createPerm: 'cases.create',
  updatePerm: 'cases.update',
  deletePerm: 'cases.delete',
  formatDoc: async (d) => {
    const work = d.work ? await Work.findById(d.work).lean() : null;
    return {
      id: d._id.toString(),
      title: d.title,
      work_id: d.work?.toString?.() || d.work,
      work_title: work?.title || '—',
      case_status: d.caseStatus,
      priority: d.priority,
      description: d.description,
      notes: d.notes || [],
    };
  },
  beforeCreate: (body) => ({
    title: body.title,
    work: body.work || body.work_id || undefined,
    usageReport: body.usageReport || body.usage_report_id || undefined,
    caseStatus: body.caseStatus || body.case_status || 'open',
    priority: body.priority || 'medium',
    description: body.description || '',
  }),
});
