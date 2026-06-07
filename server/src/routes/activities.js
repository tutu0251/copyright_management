import { Router } from 'express';
import { AuditLog } from '../models/AuditLog.js';
import { requireAuth, requirePermission } from '../middleware/auth.js';

const router = Router();
router.use(requireAuth, requirePermission('activities.view'));

router.get('/', async (req, res) => {
  const limit = Math.min(100, Number(req.query.limit) || 50);
  const logs = await AuditLog.find()
    .sort({ createdAt: -1 })
    .limit(limit)
    .populate('actor', 'displayName email')
    .lean();
  res.json({
    items: logs.map((r) => ({
      id: r._id.toString(),
      time: r.createdAt,
      actor: r.actor?.displayName || r.actor?.email || '—',
      action: r.actionType,
      entity: r.entityType ? `${r.entityType} ${r.entityId || ''}`.trim() : '—',
      metadata: r.metadata,
    })),
  });
});

export default router;
