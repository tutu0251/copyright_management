<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\InfringementCaseModel;
use App\Models\LicenseeModel;
use App\Models\LicenseModel;
use App\Models\UsageReportModel;
use App\Models\WorkModel;

class Dashboard extends BaseController
{
    protected $helpers = ['form', 'url', 'auth'];

    private const CHART_MONTHS = 12;

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'       => 'Dashboard',
            'currentPage'     => 'dashboard',
            'currentUser'     => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'             => [
                ['id' => 'dashboard', 'label' => 'Dashboard', 'path' => 'dashboard'],
                ['id' => 'assets', 'label' => 'Assets', 'path' => 'works'],
                ['id' => 'owners', 'label' => 'Owners', 'path' => 'owners'],
                ['id' => 'licensees', 'label' => 'Licensees', 'path' => 'licensees'],
                ['id' => 'licenses', 'label' => 'Licenses', 'path' => 'licenses'],
                ['id' => 'usage_reports', 'label' => 'Usage reports', 'path' => 'usage-reports'],
                ['id' => 'cases', 'label' => 'Cases', 'path' => 'cases'],
                ['id' => 'activities', 'label' => 'Activity', 'path' => 'activities'],
                ['id' => 'reports', 'label' => 'Reports', 'path' => 'mockup/reports'],
                ['id' => 'settings', 'label' => 'Settings', 'path' => 'mockup/settings'],
            ],
            'useAuthLogout'   => true,
            'useCharts'       => false,
            'chartPayload'    => null,
            'appCrumb'        => 'Copyright Management · Signed in',
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    /**
     * @return list<array{ym: string, label: string}>
     */
    private function chartMonthAxis(int $months): array
    {
        $months = max(1, min(24, $months));
        $rows   = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $ym    = date('Y-m', strtotime('-' . $i . ' months'));
            $rows[] = [
                'ym'    => $ym,
                'label' => date('M Y', strtotime($ym . '-01')),
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, int|float> $ymMap
     * @return list<int|float>
     */
    private function alignYmSeries(array $monthAxis, array $ymMap): array
    {
        $out = [];
        foreach ($monthAxis as $row) {
            $ym    = $row['ym'];
            $out[] = $ymMap[$ym] ?? 0;
        }

        return $out;
    }

    public function index(): string
    {
        $db = db_connect();

        $rangeDays = (int) ($this->request->getGet('range') ?? 30);
        if (! in_array($rangeDays, [7, 30, 90], true)) {
            $rangeDays = 30;
        }

        $workModel = model(WorkModel::class);
        $workType  = trim((string) ($this->request->getGet('work_type') ?? ''));
        $types     = $workModel->distinctWorkTypes();
        if ($workType !== '' && ! in_array($workType, $types, true)) {
            $workType = '';
        }

        $rangeSince     = date('Y-m-d H:i:s', strtotime('-' . $rangeDays . ' days'));
        $licenseSince   = $rangeSince;
        $usageSince     = $rangeSince;
        $auditFeedSince = $rangeSince;

        $licTable = $db->prefixTable('licenses');

        $totalLicenses = (int) $db->table('licenses')->where('deleted_at', null)->countAllResults();

        $activeLicensesRow = $db->query(
            "SELECT COUNT(*) AS c FROM `{$licTable}` lic
            WHERE lic.deleted_at IS NULL
            AND lic.license_status NOT IN ('draft','cancelled')
            AND (lic.end_date IS NULL OR lic.end_date >= CURDATE())",
        )->getRowArray();
        $activeLicenses = (int) ($activeLicensesRow['c'] ?? 0);

        $expiringRow = $db->query(
            "SELECT COUNT(*) AS c FROM `{$licTable}` lic
            WHERE lic.deleted_at IS NULL
            AND lic.end_date IS NOT NULL
            AND lic.end_date >= CURDATE()
            AND lic.end_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            AND lic.license_status NOT IN ('draft','cancelled')",
        )->getRowArray();
        $expiringLicenses30 = (int) ($expiringRow['c'] ?? 0);

        $revenueRow = $db->query(
            "SELECT COALESCE(SUM(lic.fee_amount), 0) AS s FROM `{$licTable}` lic
            WHERE lic.deleted_at IS NULL AND lic.payment_status = ?",
            [LicenseModel::PAYMENT_PAID],
        )->getRowArray();
        $licenseRevenue = (float) ($revenueRow['s'] ?? 0);

        $unpaidRow = $db->query(
            "SELECT COALESCE(SUM(lic.fee_amount), 0) AS s FROM `{$licTable}` lic
            WHERE lic.deleted_at IS NULL AND lic.payment_status IN (?, ?)",
            [LicenseModel::PAYMENT_UNPAID, LicenseModel::PAYMENT_PARTIAL],
        )->getRowArray();
        $licenseUnpaid = (float) ($unpaidRow['s'] ?? 0);

        $caseModel     = model(InfringementCaseModel::class);
        $casesSchemaOk = InfringementCaseModel::schemaReady();
        $totalCases    = $casesSchemaOk ? (int) $caseModel->countAll() : 0;
        $openCases     = $casesSchemaOk ? $caseModel->countOpen() : 0;
        $highPriOpen   = $casesSchemaOk ? $caseModel->countHighPriorityOpen() : 0;
        $resolvedCases = $casesSchemaOk ? $caseModel->countResolved() : 0;
        $casesByStatus = $casesSchemaOk ? $caseModel->countsByStatus() : [];

        $usageTotal        = 0;
        $usageSuspected    = 0;
        $usageInfringement = 0;
        $recentUsageDetections = [];
        if ($db->tableExists('usage_reports') && $db->fieldExists('work_id', 'usage_reports')) {
            $usageTotal = (int) $db->table('usage_reports')->where('deleted_at', null)->countAllResults();
            $usageSuspected = (int) $db->table('usage_reports')
                ->where('deleted_at', null)
                ->where('usage_type', UsageReportModel::USAGE_SUSPECTED)
                ->countAllResults();
            $usageInfringement = (int) $db->table('usage_reports')
                ->where('deleted_at', null)
                ->where('usage_type', UsageReportModel::USAGE_INFRINGEMENT)
                ->countAllResults();
            $rawRecent = model(UsageReportModel::class)->listRecentDetections($rangeDays, 12);
            foreach ($rawRecent as $rr) {
                $slug = (string) ($rr['usage_type'] ?? '');
                $recentUsageDetections[] = [
                    'id'          => (int) ($rr['id'] ?? 0),
                    'work_title'  => (string) ($rr['work_title'] ?? '—'),
                    'source'      => (string) ($rr['detected_source'] ?? ''),
                    'detected_at' => (string) ($rr['detected_at'] ?? ''),
                    'usage_label' => UsageReportModel::usageTypeLabel($slug),
                    'usage_tone'  => UsageReportModel::usageTypeBadgeTone($slug),
                ];
            }
        }

        $worksCount = (int) $workModel->countAllResults();
        if ($workType !== '') {
            $worksCount = (int) $workModel->where('deleted_at', null)->where('work_type', $workType)->countAllResults();
        }

        $ownersCount       = 0;
        $worksMultiOwners  = 0;
        $worksWithoutOwner = 0;

        if ($db->tableExists('owners')) {
            $ownersCount = $db->fieldExists('deleted_at', 'owners')
                ? (int) $db->table('owners')->where('deleted_at', null)->countAllResults()
                : (int) $db->table('owners')->countAllResults();
        }

        if ($db->tableExists('work_owners')) {
            $woTable = $db->prefixTable('work_owners');
            $wTable  = $db->prefixTable('works');
            $typeF   = $workType !== '' ? ' AND w.work_type = ' . $db->escape($workType) : '';
            $multiRow = $db->query(
                "SELECT COUNT(*) AS c FROM (
                    SELECT wo.work_id FROM `{$woTable}` wo
                    INNER JOIN `{$wTable}` w ON w.id = wo.work_id AND w.deleted_at IS NULL{$typeF}
                    WHERE wo.deleted_at IS NULL
                    GROUP BY wo.work_id HAVING COUNT(wo.id) > 1
                ) t",
            )->getRowArray();
            $worksMultiOwners = (int) ($multiRow['c'] ?? 0);

            $noOwnerRow = $db->query(
                "SELECT COUNT(*) AS c FROM `{$wTable}` w
                WHERE w.deleted_at IS NULL{$typeF}
                AND NOT EXISTS (
                    SELECT 1 FROM `{$woTable}` wo
                    WHERE wo.work_id = w.id AND wo.deleted_at IS NULL
                )",
            )->getRowArray();
            $worksWithoutOwner = (int) ($noOwnerRow['c'] ?? 0);
        }

        $pinnedQuery = $workModel
            ->select('id, title, copyright_status')
            ->where('deleted_at', null);
        if ($workType !== '') {
            $pinnedQuery->where('work_type', $workType);
        }
        $pinnedRows = $pinnedQuery->orderBy('updated_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->findAll();

        $pinnedWorks = [];
        foreach ($pinnedRows as $row) {
            $pinnedWorks[] = [
                'work_id'          => (string) $row['id'],
                'title'            => (string) $row['title'],
                'copyright_status' => (string) $row['copyright_status'],
            ];
        }

        $licenseModel = model(LicenseModel::class);
        $monthAxis    = $this->chartMonthAxis(self::CHART_MONTHS);

        $worksByMonth = $workModel->countNewWorksByMonth(self::CHART_MONTHS, $workType !== '' ? $workType : null);
        $registeredWorksSeries = array_map(static fn ($v) => (int) $v, $this->alignYmSeries($monthAxis, $worksByMonth));

        $licActivity = $licenseModel->monthlyActiveVsExpiredEndOfMonth(self::CHART_MONTHS, $workType !== '' ? $workType : null);
        $activeLicSeries  = $licActivity['active'];
        $expiredLicSeries = $licActivity['expired'];

        $feeByMonth = $licenseModel->sumPaidFeesByLicenseCreatedMonth(self::CHART_MONTHS, $workType !== '' ? $workType : null);
        $revenueChart = [];
        foreach ($monthAxis as $row) {
            $ym            = $row['ym'];
            $revenueChart[] = [
                'month'  => $row['label'],
                'amount' => round((float) ($feeByMonth[$ym] ?? 0), 2),
            ];
        }

        $openedByMonth   = $casesSchemaOk ? $caseModel->countOpenedByMonth(self::CHART_MONTHS) : [];
        $resolvedByMonth = $casesSchemaOk ? $caseModel->countResolvedByMonth(self::CHART_MONTHS) : [];
        $infDetected     = array_map(static fn ($v) => (int) $v, $this->alignYmSeries($monthAxis, $openedByMonth));
        $infResolved     = array_map(static fn ($v) => (int) $v, $this->alignYmSeries($monthAxis, $resolvedByMonth));

        $labels = array_column($monthAxis, 'label');

        $caseStatusLabels = [];
        $caseStatusValues = [];
        if ($casesByStatus !== []) {
            foreach (InfringementCaseModel::ALL_STATUSES as $st) {
                $caseStatusLabels[] = InfringementCaseModel::statusLabel($st);
                $caseStatusValues[] = $casesByStatus[$st] ?? 0;
            }
        }

        $chartPayload = [
            'labels'              => $labels,
            'registeredWorks'     => $registeredWorksSeries,
            'activeLicenses'      => $activeLicSeries,
            'expiredLicenses'     => $expiredLicSeries,
            'infringement'        => [
                'detected'  => $infDetected,
                'resolved'  => $infResolved,
            ],
            'revenue'             => $revenueChart,
            'caseStatusLabels'    => $caseStatusLabels,
            'caseStatusValues'    => $caseStatusValues,
        ];

        $activityFeed   = [];
        $auditTodayCount = 0;
        $auditTopLabel   = '—';
        $auditFeedLive   = AuditLogModel::schemaReady();
        if ($auditFeedLive) {
            $auditModel = model(AuditLogModel::class);
            $dayStart   = date('Y-m-d 00:00:00');
            $auditTodayCount = $auditModel->countSince($dayStart);
            $topUsers        = $auditModel->topUsersSince($dayStart, 5);
            if ($topUsers !== []) {
                $parts = [];
                foreach (array_slice($topUsers, 0, 3) as $u) {
                    $nm = (string) ($u['display_name'] ?? '');
                    if ($nm === '') {
                        $nm = (string) ($u['email'] ?? 'User');
                    }
                    $parts[] = $nm . ' (' . (string) ($u['action_count'] ?? 0) . ')';
                }
                $auditTopLabel = implode(', ', $parts);
            }
            $rawFeed = $auditModel->listRecentWithUsers(20, 0, $auditFeedSince);
            foreach ($rawFeed as $r) {
                $actor = (string) ($r['actor_name'] ?? '') !== ''
                    ? (string) $r['actor_name']
                    : ((string) ($r['actor_email'] ?? '') !== '' ? (string) $r['actor_email'] : '—');
                $action = (string) ($r['action_type'] ?? '');
                $et     = (string) ($r['entity_type'] ?? '');
                $eid    = isset($r['entity_id']) && $r['entity_id'] !== null && $r['entity_id'] !== '' ? (int) $r['entity_id'] : 0;
                $elab   = $et !== '' ? ($eid > 0 ? $et . ' #' . $eid : $et) : '—';
                $activityFeed[] = [
                    'time'   => (string) ($r['created_at'] ?? ''),
                    'actor'  => $actor,
                    'action' => $action,
                    'entity' => $elab,
                    'type'   => $et !== '' ? preg_replace('/[^a-z]/i', '', $et) : 'audit',
                ];
            }
        }

        $topLicensedWorks = $licenseModel->topLicensedWorks(8, $workType !== '' ? $workType : null, $licenseSince);
        $topLicensees     = model(LicenseeModel::class)->topByNewLicensesSince($licenseSince, 8, $workType !== '' ? $workType : null);
        $topReportedWorks = UsageReportModel::monitoringSchemaReady()
            ? model(UsageReportModel::class)->topWorksByReportCount(8, $usageSince, $workType !== '' ? $workType : null)
            : [];

        $dashboardUrl = site_url('dashboard');

        $primaryStats = [
            [
                'label' => 'Total works',
                'value' => (string) $worksCount,
                'hint'  => $workType !== '' ? 'Filtered by work type' : 'Assets in your catalog',
                'kpi'   => 'works',
                'kpi_href' => site_url('works'),
            ],
            [
                'label' => 'Total owners',
                'value' => (string) $ownersCount,
                'hint'  => 'Parties in the registry',
                'kpi'   => 'owners',
                'kpi_href' => site_url('owners'),
            ],
            [
                'label' => 'Total licenses',
                'value' => (string) $totalLicenses,
                'hint'  => 'All license records (excluding deleted)',
                'kpi'   => 'lic_total',
                'kpi_href' => site_url('licenses'),
            ],
            [
                'label' => 'Active licenses',
                'value' => (string) $activeLicenses,
                'hint'  => 'In force (not draft/cancelled, not past end date)',
                'kpi'   => 'lic_active',
                'kpi_href' => site_url('licenses'),
            ],
            [
                'label' => 'Expiring within 30 days',
                'value' => (string) $expiringLicenses30,
                'hint'  => 'End date in the next month',
                'kpi'   => 'lic_exp',
                'kpi_href' => site_url('licenses'),
            ],
            [
                'label' => 'Total revenue (paid fees)',
                'value' => '$' . number_format($licenseRevenue, 2),
                'hint'  => 'Sum of fee where payment is paid',
                'kpi'   => 'lic_rev',
                'kpi_href' => site_url('licenses'),
            ],
            [
                'label' => 'Unpaid license fees',
                'value' => '$' . number_format($licenseUnpaid, 2),
                'hint'  => 'Unpaid or partial payment',
                'kpi'   => 'lic_unpaid',
                'kpi_href' => site_url('licenses'),
            ],
            [
                'label' => 'Usage reports (total)',
                'value' => (string) $usageTotal,
                'hint'  => 'Monitoring entries on file',
                'kpi'   => 'usage_total',
                'kpi_href' => site_url('usage-reports'),
            ],
            [
                'label' => 'Infringement reports',
                'value' => (string) $usageInfringement,
                'hint'  => 'Usage reports flagged as infringement',
                'kpi'   => 'usage_infringement',
                'kpi_href' => site_url('usage-reports?usage_type=infringement'),
            ],
            [
                'label' => 'Open cases',
                'value' => (string) $openCases,
                'hint'  => 'Infringement cases not resolved or rejected',
                'kpi'   => 'cases_open',
                'kpi_href' => site_url('cases'),
            ],
            [
                'label' => 'Resolved cases',
                'value' => (string) $resolvedCases,
                'hint'  => 'Cases marked resolved',
                'kpi'   => 'cases_resolved',
                'kpi_href' => site_url('cases?case_status=resolved'),
            ],
        ];

        $extraStats = [
            [
                'label' => 'Total infringement cases',
                'value' => (string) $totalCases,
                'hint'  => 'All case records',
                'kpi'   => 'cases_total',
                'kpi_href' => site_url('cases'),
            ],
            [
                'label' => 'High-priority open cases',
                'value' => (string) $highPriOpen,
                'hint'  => 'Open cases marked high or critical',
                'kpi'   => 'cases_high',
                'kpi_href' => site_url('cases'),
            ],
            [
                'label' => 'Suspected usage',
                'value' => (string) $usageSuspected,
                'hint'  => 'Reports flagged as suspected',
                'kpi'   => 'usage_suspected',
                'kpi_href' => site_url('usage-reports?usage_type=suspected'),
            ],
            [
                'label' => 'Works with multiple owners',
                'value' => (string) $worksMultiOwners,
                'hint'  => 'More than one linked owner',
                'kpi'   => 'multi',
                'kpi_href' => site_url('works'),
            ],
            [
                'label' => 'Works without owner link',
                'value' => (string) $worksWithoutOwner,
                'hint'  => 'No work_owners rows yet',
                'kpi'   => 'noowner',
                'kpi_href' => site_url('works'),
            ],
        ];

        if ($auditFeedLive) {
            $extraStats[] = [
                'label' => 'Audit events today',
                'value' => (string) $auditTodayCount,
                'hint'  => 'Since midnight (all users)',
                'kpi'   => 'audit_today',
                'kpi_href' => site_url('activities'),
            ];
            $extraStats[] = [
                'label' => 'Most active users today',
                'value' => $auditTopLabel,
                'hint'  => 'Top three by audit count',
                'kpi'   => 'audit_top',
                'kpi_href' => site_url('activities'),
            ];
        }

        return $this->layout('dashboard/index', [
            'pageTitle'             => 'Dashboard',
            'stats'                 => $primaryStats,
            'extraStats'            => $extraStats,
            'useCharts'             => true,
            'chartPayload'          => $chartPayload,
            'activity'              => $activityFeed,
            'auditFeedLive'         => $auditFeedLive,
            'pinnedWorks'           => $pinnedWorks,
            'recentUsageDetections' => $recentUsageDetections,
            'caseStatusBreakdown'   => $casesByStatus,
            'casesSchemaOk'         => $casesSchemaOk,
            'dashboardRangeDays'    => $rangeDays,
            'dashboardWorkType'     => $workType,
            'dashboardWorkTypes'    => $types,
            'dashboardUrl'          => $dashboardUrl,
            'topLicensedWorks'      => $topLicensedWorks,
            'topLicensees'          => $topLicensees,
            'topReportedWorks'      => $topReportedWorks,
        ]);
    }
}
