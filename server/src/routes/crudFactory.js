const { Router } = require('express');
const { requireAuth, requirePermission } = require('../middleware/auth.js');

function createCrudRoutes({
  Model,
  viewPerm,
  createPerm,
  updatePerm,
  deletePerm,
  listFilter = { deletedAt: null },
  formatDoc = (d) => d,
  beforeCreate,
  // Optional batch enrichment: receives the raw (lean) list before formatting so
  // extra fields (e.g. asset counts) can be attached in a single query.
  decorateList,
}) {
  const router = Router();
  router.use(requireAuth);

  const fmt = async (d) => {
    const out = formatDoc(d);
    return out instanceof Promise ? out : out;
  };

  router.get('/', requirePermission(viewPerm), async (req, res) => {
    const q = { ...listFilter };
    const items = await Model.find(q).sort({ updatedAt: -1 }).limit(500).lean();
    if (decorateList) await decorateList(items, req);
    res.json({ items: await Promise.all(items.map(fmt)) });
  });

  router.get('/:id', requirePermission(viewPerm), async (req, res) => {
    const doc = await Model.findOne({ _id: req.params.id, ...listFilter }).lean();
    if (!doc) return res.status(404).json({ error: 'Not found' });
    res.json({ item: await fmt(doc) });
  });

  router.post('/', requirePermission(createPerm), async (req, res) => {
    const body = beforeCreate ? beforeCreate(req.body, req) : req.body;
    const doc = await Model.create(body);
    res.status(201).json({ item: await fmt(doc.toObject()) });
  });

  router.put('/:id', requirePermission(updatePerm), async (req, res) => {
    const doc = await Model.findOneAndUpdate(
      { _id: req.params.id, ...listFilter },
      { $set: req.body },
      { new: true },
    );
    if (!doc) return res.status(404).json({ error: 'Not found' });
    res.json({ item: await fmt(doc.toObject()) });
  });

  router.delete('/:id', requirePermission(deletePerm), async (req, res) => {
    const doc = await Model.findOneAndUpdate(
      { _id: req.params.id, ...listFilter },
      { $set: { deletedAt: new Date() } },
      { new: true },
    );
    if (!doc) return res.status(404).json({ error: 'Not found' });
    res.json({ ok: true });
  });

  return router;
}

module.exports = { createCrudRoutes };
