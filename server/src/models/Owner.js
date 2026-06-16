const { createModel } = require('../lib/model.js');

const Owner = createModel('Owner', {
  collection: 'owners',
  timestamps: true,
  fields: {
    legalName: {},
    entityType: { default: 'individual' },
    email: { default: '' },
    deletedAt: { default: null },
  },
});

module.exports = { Owner };
