<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\CopyrightMockData;
use App\Models\InfringementCaseModel;
use App\Models\LicenseModel;
use App\Models\UsageReportModel;
use App\Models\WorkModel;

class Dashboard extends BaseController
{
    protected $helpers = ['form', 'url', 'auth'];

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
                ['id' => 'reports', 'label' => 'Reports', 'path' => 'mockup/reports'],
                ['id' => 'settings', 'label' => 'Settings', 'path' => 'mockup/settings'],
            ],
            'useAuthLogout'   => true,
            'useCharts'       => false,
            'chartPayload'    => null,
            'appCrumb'        => 'Copyright Management · Signed in',
        ];

        $payload           = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        $db        = db_connect();
        $workModel = model(WorkModel::class);

        $licTable = $db->prefixTable('licenses');

        $totalLicenses = $db->table('licenses')->where('deleted_at', null)->countAllResults();

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
        $caseModel       = model(InfringementCaseModel::class);
        $casesSchemaOk   = InfringementCaseModel::schemaReady();
        $totalCases      = $casesSchemaOk ? (int) $caseModel->countAll() : 0;
        $openCases       = $casesSchemaOk ? $caseModel->countOpen() : 0;
        $highPriOpen     = $casesSchemaOk ? $caseModel->countHighPriorityOpen() : 0;
        $resolvedCases   = $casesSchemaOk ? $caseModel->countResolved() : 0;
        $casesByStatus   = $casesSchemaOk ? $caseModel->countsByStatus() : [];

        $usageTotal          = 0;
        $usageSuspected      = 0;
        $usageInfringement   = 0;
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
            $rawRecent = model(UsageReportModel::class)->listRecentDetections(7, 12);
            foreach ($rawRecent as $rr) {
                $slug = (string) ($rr['usage_type'] ?? '');
                $recentUsageDetections[] = [
                    'id'           => (int) ($rr['id'] ?? 0),
                    'work_title'   => (string) ($rr['work_title'] ?? '—'),
                    'source'       => (string) ($rr['detected_source'] ?? ''),
                    'detected_at'  => (string) ($rr['detected_at'] ?? ''),
                    'usage_label'  => UsageReportModel::usageTypeLabel($slug),
                    'usage_tone'   => UsageReportModel::usageTypeBadgeTone($slug),
                ];
            }
        }

        $worksCount = (int) $workModel->countAllResults();

        $ownersCount         = 0;
        $worksMultiOwners    = 0;
        $worksWithoutOwner   = 0;

        if ($db->tableExists('owners')) {
            $ownersCount = $db->fieldExists('deleted_at', 'owners')
                ? $db->table('owners')->where('deleted_at', null)->countAllResults()
                : $db->table('owners')->countAllResults();
        }

        if ($db->tableExists('work_owners')) {
            $woTable = $db->prefixTable('work_owners');
            $wTable  = $db->prefixTable('works');
            $multiRow = $db->query(
                "SELECT COUNT(*) AS c FROM (
                    SELECT wo.work_id FROM `{$woTable}` wo
                    INNER JOIN `{$wTable}` w ON w.id = wo.work_id AND w.deleted_at IS NULL
                    WHERE wo.deleted_at IS NULL
                    GROUP BY wo.work_id HAVING COUNT(wo.id) > 1
                ) t",
            )->getRowArray();
            $worksMultiOwners = (int) ($multiRow['c'] ?? 0);

            $noOwnerRow = $db->query(
                "SELECT COUNT(*) AS c FROM `{$wTable}` w
                WHERE w.deleted_at IS NULL
                AND NOT EXISTS (
                    SELECT 1 FROM `{$woTable}` wo
                    WHERE wo.work_id = w.id AND wo.deleted_at IS NULL
                )",
            )->getRowArray();
            $worksWithoutOwner = (int) ($noOwnerRow['c'] ?? 0);
        }

        $pinnedRows = $workModel
            ->select('id, title, copyright_status')
            ->orderBy('updated_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->findAll();

        $pinnedWorks = [];
        foreach ($pinnedRows as $row) {
            $pinnedWorks[] = [
                'work_id'           => (string) $row['id'],
                'title'             => (string) $row['title'],
                'copyright_status'  => (string) $row['copyright_status'],
            ];
        }

        $labels = CopyrightMockData::chartMonthLabels();
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
            'registeredWorks'     => CopyrightMockData::registeredWorksMonthly(),
            'activeLicenses'      => CopyrightMockData::activeLicensesMonthly(),
            'infringement'        => CopyrightMockData::infringementMonthly(),
            'revenue'             => CopyrightMockData::licenseRevenueMonthly(),
            'caseStatusLabels'    => $caseStatusLabels,
            'caseStatusValues'    => $caseStatusValues,
        ];

        $stats = [
            [
                'label' => 'Registered works',
                'value' => (string) $worksCount,
                'hint'  => 'Assets in your catalog',
                'kpi'   => 'works',
                'kpi_href' => site_url('works'),
            ],
            [
                'label' => 'Owners in registry',
                'value' => (string) $ownersCount,
                'hint'  => 'Parties you can link to assets',
                'kpi'   => 'owners',
                'kpi_href' => site_url('owners'),
            ],
            [
                'label' => 'Works with multiple owners',
                'value' => (string) $worksMultiOwners,
                'hint'  => 'Assets with more than one linked owner',
                'kpi'   => 'multi',
                'kpi_href' => site_url('works'),
            ],
            [
                'label' => 'Works without owner link',
                'value' => (string) $worksWithoutOwner,
                'hint'  => 'No rows in work ownership yet',
                'kpi'   => 'noowner',
                'kpi_href' => site_url('works'),
            ],
            [
                'label' => 'Total licenses',
                'value' => (string) $totalLicenses,
                'hint'  => 'All license records (excluding archived)',
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
                'hint'  => 'End date in the next month (excluding draft/cancelled)',
                'kpi'   => 'lic_exp',
                'kpi_href' => site_url('licenses'),
            ],
            [
                'label' => 'License revenue (paid)',
                'value' => '$' . number_format($licenseRevenue, 2),
                'hint'  => 'Sum of fee where payment is marked paid',
                'kpi'   => 'lic_rev',
                'kpi_href' => site_url('licenses'),
            ],
            [
                'label' => 'Unpaid license fees',
                'value' => '$' . number_format($licenseUnpaid, 2),
                'hint'  => 'Sum of fee where payment is unpaid or partial',
                'kpi'   => 'lic_unpaid',
                'kpi_href' => site_url('licenses'),
            ],
            [
                'label' => 'Infringement cases (total)',
                'value' => (string) $totalCases,
                'hint'  => 'All case records in workflow',
                'kpi'   => 'cases_total',
                'kpi_href' => site_url('cases'),
            ],
            [
                'label' => 'Open infringement cases',
                'value' => (string) $openCases,
                'hint'  => 'Excluding resolved and rejected',
                'kpi'   => 'cases_open',
                'kpi_href' => site_url('cases'),
            ],
            [
                'label' => 'High-priority open cases',
                'value' => (string) $highPriOpen,
                'hint'  => 'Open cases marked high or critical priority',
                'kpi'   => 'cases_high',
                'kpi_href' => site_url('cases'),
            ],
            [
                'label' => 'Resolved cases',
                'value' => (string) $resolvedCases,
                'hint'  => 'Cases marked resolved',
                'kpi'   => 'cases_resolved',
                'kpi_href' => site_url('cases?case_status=resolved'),
            ],
            [
                'label' => 'Usage reports (total)',
                'value' => (string) $usageTotal,
                'hint'  => 'Work-level detections on file',
                'kpi'   => 'usage_total',
                'kpi_href' => site_url('usage-reports'),
            ],
            [
                'label' => 'Suspected usage',
                'value' => (string) $usageSuspected,
                'hint'  => 'Reports flagged as suspected',
                'kpi'   => 'usage_suspected',
                'kpi_href' => site_url('usage-reports?usage_type=suspected'),
            ],
            [
                'label' => 'Infringement usage',
                'value' => (string) $usageInfringement,
                'hint'  => 'Reports flagged as infringement',
                'kpi'   => 'usage_infringement',
                'kpi_href' => site_url('usage-reports?usage_type=infringement'),
            ],
        ];

        return $this->layout('dashboard/index', [
            'pageTitle'             => 'Dashboard',
            'stats'                 => $stats,
            'useCharts'             => true,
            'chartPayload'          => $chartPayload,
            'activity'              => CopyrightMockData::recentActivity(),
            'pinnedWorks'           => $pinnedWorks,
            'recentUsageDetections' => $recentUsageDetections,
            'caseStatusBreakdown'   => $casesByStatus,
            'casesSchemaOk'         => $casesSchemaOk,
        ]);
    }
}
