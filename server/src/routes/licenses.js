import { createCrudRoutes } from './crudFactory.js';
import { License } from '../models/License.js';
import { Work } from '../models/Work.js';
import { Licensee } from '../models/Licensee.js';

export default createCrudRoutes({
  Model: License,
  viewPerm: 'licenses.view',
  createPerm: 'licenses.create',
  updatePerm: 'licenses.update',
  deletePerm: 'licenses.delete',
  formatDoc: async (d) => {
    const work = d.work ? await Work.findById(d.work).lean() : null;
    const licensee = d.licensee ? await Licensee.findById(d.licensee).lean() : null;
    return {
      id: d._id.toString(),
      work_id: d.work?.toString?.() || d.work,
      work_title: work?.title || '—',
      licensee_id: d.licensee?.toString?.() || d.licensee,
      licensee_name: licensee?.name || '—',
      license_type: d.licenseType,
      license_status: d.licenseStatus,
      payment_status: d.paymentStatus,
      fee_amount: d.feeAmount,
      territory: d.territory,
      start_date: d.startDate,
      end_date: d.endDate,
    };
  },
});
