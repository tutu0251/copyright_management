const { createCrudRoutes } = require('./crudFactory.js');
const { Licensee } = require('../models/Licensee.js');

module.exports = createCrudRoutes({
  Model: Licensee,
  viewPerm: 'licensees.view',
  createPerm: 'licensees.create',
  updatePerm: 'licensees.update',
  deletePerm: 'licensees.delete',
  formatDoc: (d) => ({
    id: d._id.toString(),
    name: d.name,
    contact_email: d.contactEmail,
    contactEmail: d.contactEmail,
    organization: d.organization,
    notes: d.notes,
  }),
});
