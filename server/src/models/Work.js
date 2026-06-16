const { createModel } = require('../lib/model.js');

const Work = createModel('Work', {
  collection: 'works',
  timestamps: true,
  fields: {
    title: {},
    slug: {},
    workType: { default: 'Text' },
    creator: { default: '' },
    owner: { default: '' },
    copyrightStatus: { default: 'draft' },
    riskLevel: { default: 'Low' },
    description: { default: '' },
    registeredAt: {},
    createdBy: { ref: 'User' },
    deletedAt: { default: null },
  },
});

module.exports = { Work };
