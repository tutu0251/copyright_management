const { Router } = require('express');
const { Work } = require('../models/Work.js');
const { License } = require('../models/License.js');
const { createCrudRoutes } = require('./crudFactory.js');
const { requireAuth, requirePermission } = require('../middleware/auth.js');

const crud = createCrudRoutes({
  Model: Work,
  viewPerm: 'works.view',
  createPerm: 'works.create',
  updatePerm: 'works.update',
  deletePerm: 'works.delete',
  formatDoc: (d) => ({
    id: d._id.toString(),
    work_id: d._id.toString(),
    title: d.title,
    type: d.workType,
    workType: d.workType,
    creator: d.creator,
    owner: d.owner,
    copyright_status: d.copyrightStatus,
    copyrightStatus: d.copyrightStatus,
    risk_level: d.riskLevel,
    riskLevel: d.riskLevel,
    description: d.description,
    registered_at: d.registeredAt,
    registration_date: d.registeredAt ? new Date(d.registeredAt).toISOString().slice(0, 10) : '',
    last_updated: d.updatedAt,
    license_count: 0,
  }),
  beforeCreate: (body, req) => ({
    title: body.title,
    workType: body.workType || body.work_type || 'Text',
    creator: body.creator || '',
    owner: body.owner || '',
    copyrightStatus: body.copyrightStatus || body.copyright_status || 'draft',
    riskLevel: body.riskLevel || body.risk_level || 'Low',
    description: body.description || '',
    registeredAt: body.registeredAt || body.registered_at || null,
    createdBy: req.user._id,
  }),
});

const router = Router();
router.use(crud);
router.get('/meta/types', requireAuth, requirePermission('works.view'), async (_req, res) => {
  const types = await Work.distinct('workType', { deletedAt: null });
  res.json({ types: types.filter(Boolean) });
});

module.exports = router;
