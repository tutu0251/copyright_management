const { createCrudRoutes } = require('./crudFactory.js');
const { License } = require('../models/License.js');
const { Work } = require('../models/Work.js');
const { Licensee } = require('../models/Licensee.js');

module.exports = createCrudRoutes({
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
      work_id: (d.work && d.work.toString ? d.work.toString() : d.work),
      work_title: (work && work.title) || '—',
      licensee_id: (d.licensee && d.licensee.toString ? d.licensee.toString() : d.licensee),
      licensee_name: (licensee && licensee.name) || '—',
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
