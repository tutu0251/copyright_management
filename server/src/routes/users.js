import { Router } from 'express';
import { User } from '../models/User.js';
import { Role } from '../models/Role.js';
import { requireAuth, requirePermission, packUser } from '../middleware/auth.js';

const router = Router();
router.use(requireAuth, requirePermission('users.manage'));

router.get('/', async (_req, res) => {
  const users = await User.find().populate('roles').sort({ createdAt: -1 }).lean();
  res.json({
    items: users.map((u) => ({
      id: u._id.toString(),
      email: u.email,
      display_name: u.displayName,
      is_active: u.isActive,
      roles: (u.roles || []).map((r) => r.name),
      last_login_at: u.lastLoginAt,
    })),
  });
});

router.post('/', async (req, res) => {
  const email = String(req.body.email || '').toLowerCase().trim();
  const displayName = String(req.body.display_name || req.body.displayName || '').trim();
  const password = String(req.body.password || '');
  const roleSlug = String(req.body.role || 'viewer');
  if (!email || !displayName || password.length < 8) {
    return res.status(400).json({ error: 'Email, name, and password required' });
  }
  const role = await Role.findOne({ slug: roleSlug });
  if (!role) return res.status(400).json({ error: 'Invalid role' });
  const user = await User.create({
    email,
    displayName,
    passwordHash: await User.hashPassword(password),
    roles: [role._id],
  });
  res.status(201).json({ item: await packUser(user) });
});

router.patch('/:id/active', async (req, res) => {
  const isActive = Boolean(req.body.is_active ?? req.body.isActive);
  const user = await User.findByIdAndUpdate(req.params.id, { isActive }, { new: true });
  if (!user) return res.status(404).json({ error: 'Not found' });
  res.json({ ok: true });
});

export default router;
