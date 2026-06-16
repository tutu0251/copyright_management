const { createModel } = require('../lib/model.js');

const License = createModel('License', {
  collection: 'licenses',
  timestamps: true,
  fields: {
    work: { ref: 'Work' },
    licensee: { ref: 'Licensee' },
    licenseType: { default: 'non_exclusive' },
    licenseStatus: { default: 'draft' },
    paymentStatus: { default: 'unpaid' },
    feeAmount: { default: 0 },
    territory: { default: '' },
    startDate: {},
    endDate: {},
    deletedAt: { default: null },
  },
});

module.exports = { License };
