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
