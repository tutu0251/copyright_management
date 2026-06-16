const { Router } = require('express');
const authRoutes = require('./auth.js');
const dashboardRoutes = require('./dashboard.js');
const worksRoutes = require('./works.js');
const ownersRoutes = require('./owners.js');
const licenseesRoutes = require('./licensees.js');
const licensesRoutes = require('./licenses.js');
const usageReportsRoutes = require('./usageReports.js');
const casesRoutes = require('./cases.js');
const usersRoutes = require('./users.js');
const rolesRoutes = require('./roles.js');
const activitiesRoutes = require('./activities.js');
const reportsRoutes = require('./reports.js');
const { assetsRouter } = require('./assets.js');

const router = Router();

router.use('/auth', authRoutes);
router.use('/dashboard', dashboardRoutes);
router.use('/works', worksRoutes);
router.use('/assets', assetsRouter);
router.use('/owners', ownersRoutes);
router.use('/licensees', licenseesRoutes);
router.use('/licenses', licensesRoutes);
router.use('/usage-reports', usageReportsRoutes);
router.use('/cases', casesRoutes);
router.use('/users', usersRoutes);
router.use('/roles', rolesRoutes);
router.use('/activities', activitiesRoutes);
router.use('/reports', reportsRoutes);

router.get('/health', (_req, res) => res.json({ ok: true }));

module.exports = router;
