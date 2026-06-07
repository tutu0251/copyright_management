const { Router } = require('express');
const { Work } = require('../models/Work.js');
const { Owner } = require('../models/Owner.js');
const { License } = require('../models/License.js');
const { UsageReport } = require('../models/UsageReport.js');
const { InfringementCase } = require('../models/Case.js');
const { AuditLog } = require('../models/AuditLog.js');
const { requireAuth, requirePermission } = require('../middleware/auth.js');

const router = Router();
router.use(requireAuth, requirePermission('dashboard.view'));

function monthAxis(months = 12) {
  const rows = [];
  for (let i = months - 1; i >= 0; i--) {
    const d = new Date();
    d.setMonth(d.getMonth() - i);
    const ym = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
    rows.push({ ym, label: d.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) });
  }
  return rows;
}

router.get('/', async (req, res) => {
  const rangeDays = [7, 30, 90].includes(Number(req.query.range)) ? Number(req.query.range) : 30;
  const workType = String(req.query.work_type || '').trim();
  const workFilter = { deletedAt: null };
  if (workType) workFilter.workType = workType;

  const worksCount = await Work.countDocuments(workFilter);
  const ownersCount = await Owner.countDocuments({ deletedAt: null });
  const totalLicenses = await License.countDocuments({ deletedAt: null });
  const now = new Date();
  const activeLicenses = await License.countDocuments({
    deletedAt: null,
    licenseStatus: { $nin: ['draft', 'cancelled'] },
    $or: [{ endDate: null }, { endDate: { $gte: now } }],
  });
  const in30 = new Date(now);
  in30.setDate(in30.getDate() + 30);
  const expiringLicenses30 = await License.countDocuments({
    deletedAt: null,
    endDate: { $gte: now, $lte: in30 },
    licenseStatus: { $nin: ['draft', 'cancelled'] },
  });
  const paidAgg = await License.aggregate([
    { $match: { deletedAt: null, paymentStatus: 'paid' } },
    { $group: { _id: null, total: { $sum: '$feeAmount' } } },
  ]);
  const unpaidAgg = await License.aggregate([
    { $match: { deletedAt: null, paymentStatus: { $in: ['unpaid', 'partial'] } } },
    { $group: { _id: null, total: { $sum: '$feeAmount' } } },
  ]);
  const licenseRevenue = (paidAgg[0] && paidAgg[0].total) || 0;
  const licenseUnpaid = (unpaidAgg[0] && unpaidAgg[0].total) || 0;

  const totalCases = await InfringementCase.countDocuments({ deletedAt: null });
  const openCases = await InfringementCase.countDocuments({
    deletedAt: null,
    caseStatus: { $nin: ['resolved', 'rejected', 'closed'] },
  });
  const resolvedCases = await InfringementCase.countDocuments({ deletedAt: null, caseStatus: 'resolved' });

  const usageTotal = await UsageReport.countDocuments({ deletedAt: null });
  const usageSuspected = await UsageReport.countDocuments({ deletedAt: null, usageType: 'suspected' });
  const usageInfringement = await UsageReport.countDocuments({ deletedAt: null, usageType: 'infringement' });

  const types = await Work.distinct('workType', { deletedAt: null });
  const pinnedWorks = await Work.find(workFilter)
    .select('title copyrightStatus')
    .sort({ updatedAt: -1 })
    .limit(5)
    .lean();

  const since = new Date();
  since.setDate(since.getDate() - rangeDays);
  const recentUsage = await UsageReport.find({ deletedAt: null, detectedAt: { $gte: since } })
    .populate('work', 'title')
    .sort({ detectedAt: -1 })
    .limit(12)
    .lean();

  const axis = monthAxis(12);
  const worksByMonth = await Work.aggregate([
    { $match: { deletedAt: null, createdAt: { $gte: new Date(axis[0].ym + '-01') } } },
    {
      $group: {
        _id: { $dateToString: { format: '%Y-%m', date: '$createdAt' } },
        count: { $sum: 1 },
      },
    },
  ]);
  const ymMap = Object.fromEntries(worksByMonth.map((r) => [r._id, r.count]));
  const registeredWorks = axis.map((a) => ymMap[a.ym] || 0);

  const casesByStatus = await InfringementCase.aggregate([
    { $match: { deletedAt: null } },
    { $group: { _id: '$caseStatus', count: { $sum: 1 } } },
  ]);

  const dayStart = new Date();
  dayStart.setHours(0, 0, 0, 0);
  const auditTodayCount = await AuditLog.countDocuments({ createdAt: { $gte: dayStart } });
  const activity = await AuditLog.find()
    .sort({ createdAt: -1 })
    .limit(20)
    .populate('actor', 'displayName email')
    .lean();

  const primaryStats = [
    { label: 'Total works', value: String(worksCount), kpi: 'works' },
    { label: 'Total owners', value: String(ownersCount), kpi: 'owners' },
    { label: 'Total licenses', value: String(totalLicenses), kpi: 'lic_total' },
    { label: 'Active licenses', value: String(activeLicenses), kpi: 'lic_active' },
    { label: 'Expiring within 30 days', value: String(expiringLicenses30), kpi: 'lic_exp' },
    { label: 'Total revenue (paid fees)', value: `$${licenseRevenue.toFixed(2)}`, kpi: 'lic_rev' },
    { label: 'Unpaid license fees', value: `$${licenseUnpaid.toFixed(2)}`, kpi: 'lic_unpaid' },
    { label: 'Usage reports (total)', value: String(usageTotal), kpi: 'usage_total' },
    { label: 'Infringement reports', value: String(usageInfringement), kpi: 'usage_infringement' },
    { label: 'Open cases', value: String(openCases), kpi: 'cases_open' },
    { label: 'Resolved cases', value: String(resolvedCases), kpi: 'cases_resolved' },
  ];

  res.json({
    stats: primaryStats,
    extraStats: [
      { label: 'Total infringement cases', value: String(totalCases), kpi: 'cases_total' },
      { label: 'Suspected usage', value: String(usageSuspected), kpi: 'usage_suspected' },
      { label: 'Audit events today', value: String(auditTodayCount), kpi: 'audit_today' },
    ],
    chartPayload: {
      labels: axis.map((a) => a.label),
      registeredWorks,
      activeLicenses: axis.map(() => activeLicenses),
      expiredLicenses: axis.map(() => 0),
      infringement: { detected: axis.map(() => 0), resolved: axis.map(() => 0) },
      revenue: axis.map((a) => ({ month: a.label, amount: 0 })),
      caseStatusLabels: casesByStatus.map((c) => c._id),
      caseStatusValues: casesByStatus.map((c) => c.count),
    },
    activity: activity.map((r) => ({
      time: r.createdAt,
      actor: (r.actor && r.actor.displayName) || (r.actor && r.actor.email) || '—',
      action: r.actionType,
      entity: r.entityType ? `${r.entityType} ${r.entityId || ''}`.trim() : '—',
      type: r.entityType || 'audit',
    })),
    pinnedWorks: pinnedWorks.map((w) => ({
      work_id: w._id.toString(),
      title: w.title,
      copyright_status: w.copyrightStatus,
    })),
    recentUsageDetections: recentUsage.map((r) => ({
      id: r._id.toString(),
      work_title: (r.work && r.work.title) || '—',
      source: r.detectedSource,
      detected_at: r.detectedAt,
      usage_label: r.usageType,
    })),
    dashboardRangeDays: rangeDays,
    dashboardWorkType: workType,
    dashboardWorkTypes: types.filter(Boolean),
  });
});

module.exports = router;
