const { AuditLog } = require('../models/AuditLog.js');

async function logAudit({ actionType, entityType, entityId, actor, metadata = {} }) {
  await AuditLog.create({
    actionType,
    entityType,
    entityId: entityId != null ? String(entityId) : '',
    actor: actor || null,
    metadata,
  });
}

module.exports = { logAudit };
