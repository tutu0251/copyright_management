const { createModel } = require('../lib/model.js');

// Embedded case notes are stored inline on the document; ObjectId fields inside
// the array (author) are not auto-cast by the data layer, so callers that add
// notes should cast as needed. The current routes create cases without notes.
const InfringementCase = createModel('InfringementCase', {
  collection: 'infringementcases',
  timestamps: true,
  fields: {
    title: {},
    work: { ref: 'Work' },
    usageReport: { ref: 'UsageReport' },
    caseStatus: { default: 'open' },
    priority: { default: 'medium' },
    description: { default: '' },
    notes: { default: () => [] },
    deletedAt: { default: null },
  },
});

module.exports = { InfringementCase };
