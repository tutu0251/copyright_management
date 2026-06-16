const { createModel } = require('../lib/model.js');

const Permission = createModel('Permission', {
  collection: 'permissions',
  timestamps: true,
  fields: {
    slug: {},
    name: {},
    description: { default: '' },
  },
});

module.exports = { Permission };
