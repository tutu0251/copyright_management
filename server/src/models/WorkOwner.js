const { createModel } = require('../lib/model.js');

const WorkOwner = createModel('WorkOwner', {
  collection: 'workowners',
  timestamps: true,
  fields: {
    work: { ref: 'Work' },
    owner: { ref: 'Owner' },
    sharePercent: { default: 100 },
    deletedAt: { default: null },
  },
});

module.exports = { WorkOwner };
