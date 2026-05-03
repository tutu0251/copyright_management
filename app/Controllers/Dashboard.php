<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\CopyrightMockData;
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
                ['id' => 'licenses', 'label' => 'Licenses', 'path' => 'mockup/licenses'],
                ['id' => 'monitoring', 'label' => 'Monitoring', 'path' => 'mockup/monitoring'],
                ['id' => 'cases', 'label' => 'Cases', 'path' => 'mockup/cases'],
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

        $activeLicenses = $db->table('licenses')->where('status', 'active')->countAllResults();
        $openCases      = $db->table('infringement_cases')
            ->whereNotIn('status', ['closed', 'resolved'])
            ->countAllResults();

        $usageReportsCount = $db->table('usage_reports')->countAllResults();

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
        $chartPayload = [
            'labels'              => $labels,
            'registeredWorks'     => CopyrightMockData::registeredWorksMonthly(),
            'activeLicenses'      => CopyrightMockData::activeLicensesMonthly(),
            'infringement'        => CopyrightMockData::infringementMonthly(),
            'revenue'             => CopyrightMockData::licenseRevenueMonthly(),
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
                'label' => 'Active licenses',
                'value' => (string) $activeLicenses,
                'hint'  => 'Agreements marked active',
                'kpi'   => 'licenses',
            ],
            [
                'label' => 'Open infringement cases',
                'value' => (string) $openCases,
                'hint'  => 'Excluding closed and resolved',
                'kpi'   => 'cases',
            ],
            [
                'label' => 'Usage reports filed',
                'value' => (string) $usageReportsCount,
                'hint'  => 'Report rows on record',
                'kpi'   => 'usage',
            ],
        ];

        return $this->layout('dashboard/index', [
            'pageTitle'     => 'Dashboard',
            'stats'         => $stats,
            'useCharts'     => true,
            'chartPayload'  => $chartPayload,
            'activity'      => CopyrightMockData::recentActivity(),
            'pinnedWorks'   => $pinnedWorks,
        ]);
    }
}
