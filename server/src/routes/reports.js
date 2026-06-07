const { Router } = require('express');
const { Work } = require('../models/Work.js');
const { License } = require('../models/License.js');
const { UsageReport } = require('../models/UsageReport.js');
const { InfringementCase } = require('../models/Case.js');
const { requireAuth, requirePermission } = require('../middleware/auth.js');

const router = Router();
router.use(requireAuth, requirePermission('reports.view'));

router.get('/summary', async (_req, res) => {
  res.json({
    works: await Work.countDocuments({ deletedAt: null }),
    licenses: await License.countDocuments({ deletedAt: null }),
    usage_reports: await UsageReport.countDocuments({ deletedAt: null }),
    cases: await InfringementCase.countDocuments({ deletedAt: null }),
  });
});

router.get('/works', async (_req, res) => {
  const items = await Work.find({ deletedAt: null }).sort({ title: 1 }).limit(500).lean();
  res.json({ items });
});

router.get('/licenses', async (_req, res) => {
  const items = await License.find({ deletedAt: null }).populate('work licensee').sort({ updatedAt: -1 }).limit(500);
  res.json({ items });
});

module.exports = router;
