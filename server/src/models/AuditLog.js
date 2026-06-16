const { createModel } = require('../lib/model.js');

const AuditLog = createModel('AuditLog', {
  collection: 'auditlogs',
  timestamps: true,
  fields: {
    actionType: {},
    entityType: { default: '' },
    entityId: { default: '' },
    actor: { ref: 'User' },
    metadata: { default: () => ({}) },
  },
});

module.exports = { AuditLog };
