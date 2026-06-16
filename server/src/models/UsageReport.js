const { createModel } = require('../lib/model.js');

const UsageReport = createModel('UsageReport', {
  collection: 'usagereports',
  timestamps: true,
  fields: {
    work: { ref: 'Work' },
    usageType: { default: 'suspected' },
    detectedSource: { default: '' },
    detectedAt: { default: Date.now },
    notes: { default: '' },
    deletedAt: { default: null },
  },
});

module.exports = { UsageReport };
