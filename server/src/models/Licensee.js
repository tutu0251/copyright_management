const { createModel } = require('../lib/model.js');

const Licensee = createModel('Licensee', {
  collection: 'licensees',
  timestamps: true,
  fields: {
    name: {},
    contactEmail: { default: '' },
    organization: { default: '' },
    notes: { default: '' },
    deletedAt: { default: null },
  },
});

module.exports = { Licensee };
