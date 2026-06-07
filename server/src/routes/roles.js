import { Router } from 'express';
import { Role } from '../models/Role.js';
import { Permission } from '../models/Permission.js';
import { requireAuth, requirePermission } from '../middleware/auth.js';

const router = Router();
router.use(requireAuth, requirePermission('settings.manage'));

router.get('/', async (_req, res) => {
  const roles = await Role.find().populate('permissions').sort({ slug: 1 }).lean();
  res.json({
    items: roles.map((r) => ({
      id: r._id.toString(),
      slug: r.slug,
      name: r.name,
      description: r.description,
      permission_count: (r.permissions || []).length,
    })),
  });
});

router.get('/permissions', async (_req, res) => {
  const perms = await Permission.find().sort({ slug: 1 }).lean();
  res.json({ items: perms });
});

router.get('/:id', async (req, res) => {
  const role = await Role.findById(req.params.id).populate('permissions').lean();
  if (!role) return res.status(404).json({ error: 'Not found' });
  res.json({
    role: {
      id: role._id.toString(),
      slug: role.slug,
      name: role.name,
      permissions: (role.permissions || []).map((p) => p.slug),
    },
  });
});

router.put('/:id/permissions', async (req, res) => {
  const slugs = Array.isArray(req.body.permissions) ? req.body.permissions : [];
  const perms = await Permission.find({ slug: { $in: slugs } });
  const role = await Role.findByIdAndUpdate(
    req.params.id,
    { permissions: perms.map((p) => p._id) },
    { new: true },
  );
  if (!role) return res.status(404).json({ error: 'Not found' });
  res.json({ ok: true });
});

export default router;
