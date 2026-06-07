import { AuditLog } from '../models/AuditLog.js';

export async function logAudit({ actionType, entityType, entityId, actor, metadata = {} }) {
  await AuditLog.create({
    actionType,
    entityType,
    entityId: entityId != null ? String(entityId) : '',
    actor: actor || null,
    metadata,
  });
}
