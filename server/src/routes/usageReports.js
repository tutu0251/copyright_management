import { createCrudRoutes } from './crudFactory.js';
import { UsageReport } from '../models/UsageReport.js';
import { Work } from '../models/Work.js';

export default createCrudRoutes({
  Model: UsageReport,
  viewPerm: 'usage_reports.view',
  createPerm: 'usage_reports.create',
  updatePerm: 'usage_reports.update',
  deletePerm: 'usage_reports.delete',
  formatDoc: async (d) => {
    const work = d.work ? await Work.findById(d.work).lean() : null;
    return {
      id: d._id.toString(),
      work_id: d.work?.toString?.() || d.work,
      work_title: work?.title || '—',
      usage_type: d.usageType,
      detected_source: d.detectedSource,
      detected_at: d.detectedAt,
      notes: d.notes,
    };
  },
  beforeCreate: (body) => ({
    work: body.work || body.work_id,
    usageType: body.usageType || body.usage_type || 'suspected',
    detectedSource: body.detectedSource || body.detected_source || '',
    detectedAt: body.detectedAt || body.detected_at || new Date(),
    notes: body.notes || '',
  }),
});
