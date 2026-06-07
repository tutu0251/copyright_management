import mongoose from 'mongoose';

const auditLogSchema = new mongoose.Schema(
  {
    actionType: { type: String, required: true },
    entityType: { type: String, default: '' },
    entityId: { type: String, default: '' },
    actor: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
    metadata: { type: mongoose.Schema.Types.Mixed, default: {} },
  },
  { timestamps: true },
);

export const AuditLog = mongoose.model('AuditLog', auditLogSchema);
