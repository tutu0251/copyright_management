const { createModel } = require('../lib/model.js');

const Role = createModel('Role', {
  collection: 'roles',
  timestamps: true,
  fields: {
    slug: {},
    name: {},
    description: { default: '' },
    permissions: { ref: 'Permission', array: true },
  },
});

module.exports = { Role };
