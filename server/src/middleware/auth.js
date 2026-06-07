import jwt from 'jsonwebtoken';
import { User } from '../models/User.js';
import { Role } from '../models/Role.js';

export async function requireAuth(req, res, next) {
  const header = req.headers.authorization || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : null;
  if (!token) {
    return res.status(401).json({ error: 'Authentication required' });
  }
  try {
    const payload = jwt.verify(token, process.env.JWT_SECRET || 'dev-secret');
    const user = await User.findById(payload.sub).populate({
      path: 'roles',
      populate: { path: 'permissions' },
    });
    if (!user || !user.isActive) {
      return res.status(401).json({ error: 'Invalid session' });
    }
    const permissions = new Set();
    for (const role of user.roles || []) {
      for (const perm of role.permissions || []) {
        permissions.add(perm.slug);
      }
    }
    req.user = user;
    req.permissions = permissions;
    next();
  } catch {
    return res.status(401).json({ error: 'Invalid or expired token' });
  }
}

export function requirePermission(slug) {
  return (req, res, next) => {
    if (req.permissions?.has(slug)) {
      return next();
    }
    return res.status(403).json({ error: 'Forbidden', permission: slug });
  };
}

export function signToken(userId) {
  return jwt.sign({ sub: userId }, process.env.JWT_SECRET || 'dev-secret', { expiresIn: '7d' });
}

export async function packUser(user) {
  const populated = await User.findById(user._id)
    .populate({ path: 'roles', populate: { path: 'permissions' } })
    .lean();
  const roles = (populated.roles || []).map((r) => ({
    slug: r.slug,
    name: r.name,
  }));
  const permissions = new Set();
  for (const role of populated.roles || []) {
    for (const p of role.permissions || []) {
      permissions.add(p.slug);
    }
  }
  return {
    id: populated._id.toString(),
    email: populated.email,
    displayName: populated.displayName,
    isActive: populated.isActive,
    roles,
    primaryRole: roles[0]?.name || 'Member',
    permissions: [...permissions],
  };
}
