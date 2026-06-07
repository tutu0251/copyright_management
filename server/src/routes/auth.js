const { Router } = require('express');
const { User } = require('../models/User.js');
const { Role } = require('../models/Role.js');
const { packUser, requireAuth, signToken } = require('../middleware/auth.js');
const { logAudit } = require('../services/auditLog.js');

const router = Router();

router.post('/login', async (req, res) => {
  const email = String(req.body.email || '').toLowerCase().trim();
  const password = String(req.body.password || '');
  const user = await User.findOne({ email });
  if (!user || !(await user.verifyPassword(password))) {
    await logAudit({ actionType: 'login_failed', entityType: 'user', metadata: { email } });
    return res.status(401).json({ error: 'Invalid credentials' });
  }
  if (!user.isActive) {
    return res.status(401).json({ error: 'Account inactive' });
  }
  user.lastLoginAt = new Date();
  await user.save();
  await logAudit({ actionType: 'login', entityType: 'user', entityId: user._id, actor: user._id });
  const token = signToken(user._id);
  res.json({ token, user: await packUser(user) });
});

router.post('/register', async (req, res) => {
  const email = String(req.body.email || '').toLowerCase().trim();
  const displayName = String(req.body.name || req.body.displayName || '').trim();
  const password = String(req.body.password || '');
  if (!email || !displayName || password.length < 8) {
    return res.status(400).json({ error: 'Valid email, name, and password (8+ chars) required' });
  }
  const exists = await User.findOne({ email });
  if (exists) {
    return res.status(409).json({ error: 'Email already exists' });
  }
  const viewer = await Role.findOne({ slug: 'viewer' });
  if (!viewer) {
    return res.status(500).json({ error: 'Default viewer role not configured. Run seed.' });
  }
  const user = await User.create({
    email,
    displayName,
    passwordHash: await User.hashPassword(password),
    roles: [viewer._id],
  });
  await logAudit({ actionType: 'register', entityType: 'user', entityId: user._id, metadata: { email } });
  res.status(201).json({ message: 'Registration successful. Please sign in.' });
});

router.get('/me', requireAuth, async (req, res) => {
  res.json({ user: await packUser(req.user) });
});

router.post('/logout', requireAuth, async (req, res) => {
  await logAudit({ actionType: 'logout', entityType: 'user', entityId: req.user._id, actor: req.user._id });
  res.json({ message: 'Signed out' });
});

module.exports = router;
