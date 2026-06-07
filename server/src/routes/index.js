import { Router } from 'express';
import authRoutes from './auth.js';
import dashboardRoutes from './dashboard.js';
import worksRoutes from './works.js';
import ownersRoutes from './owners.js';
import licenseesRoutes from './licensees.js';
import licensesRoutes from './licenses.js';
import usageReportsRoutes from './usageReports.js';
import casesRoutes from './cases.js';
import usersRoutes from './users.js';
import rolesRoutes from './roles.js';
import activitiesRoutes from './activities.js';
import reportsRoutes from './reports.js';

const router = Router();

router.use('/auth', authRoutes);
router.use('/dashboard', dashboardRoutes);
router.use('/works', worksRoutes);
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

export default router;
