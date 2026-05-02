<?php

namespace App\Controllers;

use App\Config\CopyrightMockData;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Clickable UI mockup (static data only).
 * TODO: Replace with real controllers wired to models + auth filters.
 */
class Mockup extends BaseController
{
    protected $helpers = ['url'];

    private function render(string $view, array $data = []): string
    {
        $defaults = [
            'pageTitle'    => 'Copyright Management',
            'currentPage'  => '',
            'mockUser'     => CopyrightMockData::mockUser(),
            'useCharts'    => false,
            'chartPayload' => null,
            'permissions'  => [],
        ];

        $payload           = array_merge($defaults, $data);
        $payload['content'] = view('mockup/' . $view, $payload);

        return view('layouts/main', $payload);
    }

    public function dashboard(): string
    {
        $labels = CopyrightMockData::chartMonthLabels();
        $chartPayload = [
            'labels'              => $labels,
            'registeredWorks'     => CopyrightMockData::registeredWorksMonthly(),
            'activeLicenses'      => CopyrightMockData::activeLicensesMonthly(),
            'infringement'        => CopyrightMockData::infringementMonthly(),
            'revenue'             => CopyrightMockData::licenseRevenueMonthly(),
        ];

        return $this->render('dashboard', [
            'pageTitle'    => 'Dashboard',
            'currentPage'  => 'dashboard',
            'useCharts' => true,
            'stats'     => CopyrightMockData::dashboardStats(),
            'activity'     => CopyrightMockData::recentActivity(),
            'works'        => array_slice(CopyrightMockData::works(), 0, 5),
            'chartPayload' => $chartPayload,
        ]);
    }

    /**
     * Legacy path: /mockup/works → /mockup/assets
     */
    public function worksRedirect()
    {
        $q = $this->request->getUri()->getQuery();

        return redirect()->to(site_url('mockup/assets' . ($q !== '' ? '?' . $q : '')));
    }

    public function assets(): string
    {
        $all     = CopyrightMockData::works();
        $perPage = 10;
        $page    = max(1, (int) $this->request->getGet('page'));
        $total   = count($all);
        $pages   = (int) max(1, ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;
        $slice  = array_slice($all, $offset, $perPage);

        return $this->render('assets', [
            'pageTitle'   => 'Assets',
            'currentPage' => 'assets',
            'works'       => $slice,
            'pager'       => [
                'page'       => $page,
                'perPage'    => $perPage,
                'total'      => $total,
                'totalPages' => $pages,
            ],
        ]);
    }

    public function register(): string
    {
        return $this->render('register', [
            'pageTitle'   => 'Register Work',
            'currentPage' => 'assets',
        ]);
    }

    public function workDetail(string $id): string
    {
        $work = CopyrightMockData::workById($id);

        if ($work === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $wid = (string) ($work['work_id'] ?? $work['id'] ?? '');

        return $this->render('work_detail', [
            'pageTitle'      => $work['title'],
            'currentPage'    => 'assets',
            'work'           => $work,
            'workLicenses'   => CopyrightMockData::licensesForWork($wid),
            'workUsageRows'  => CopyrightMockData::usageRowsForWork((string) $work['title']),
            'ownershipRows'  => array_values(array_filter(
                CopyrightMockData::ownershipRows(),
                static fn ($r) => ($r['work_id'] ?? '') === $wid
            )),
        ]);
    }

    public function ownership(): string
    {
        return $this->render('ownership', [
            'pageTitle'   => 'Ownership',
            'currentPage' => 'assets',
            'rows'        => CopyrightMockData::ownershipRows(),
        ]);
    }

    public function licenses(): string
    {
        return $this->render('licenses', [
            'pageTitle'   => 'Licenses',
            'currentPage' => 'licenses',
            'licenses'    => CopyrightMockData::licenses(),
        ]);
    }

    public function licenseDetail(string $id): string
    {
        $license = CopyrightMockData::licenseDetail($id);

        if ($license === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->render('license_detail', [
            'pageTitle'   => 'License ' . $license['id'],
            'currentPage' => 'licenses',
            'license'     => $license,
        ]);
    }

    public function usageReports(): string
    {
        return $this->render('usage_reports', [
            'pageTitle'   => 'Usage Reports',
            'currentPage' => 'reports',
            'rows'        => CopyrightMockData::usageReportRows(),
        ]);
    }

    public function monitoring(): string
    {
        return $this->render('monitoring', [
            'pageTitle'   => 'Monitoring',
            'currentPage' => 'monitoring',
            'scans'       => CopyrightMockData::monitoringScans(),
        ]);
    }

    public function reports(): string
    {
        $labels = CopyrightMockData::chartMonthLabels();
        $chartPayload = [
            'labels'          => $labels,
            'registeredWorks' => CopyrightMockData::registeredWorksMonthly(),
            'activeLicenses'  => CopyrightMockData::activeLicensesMonthly(),
            'infringement'    => CopyrightMockData::infringementMonthly(),
            'revenue'         => CopyrightMockData::licenseRevenueMonthly(),
        ];

        return $this->render('reports', [
            'pageTitle'    => 'Reports',
            'currentPage'  => 'reports',
            'useCharts'    => true,
            'chartPayload' => $chartPayload,
            'usageRows'    => CopyrightMockData::usageReportRows(),
        ]);
    }

    public function cases(): string
    {
        return $this->render('cases', [
            'pageTitle'   => 'Cases',
            'currentPage' => 'cases',
            'cases'       => CopyrightMockData::infringementCases(),
        ]);
    }

    public function caseDetail(string $id): string
    {
        $case = CopyrightMockData::caseDetail($id);

        if ($case === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->render('case_detail', [
            'pageTitle'   => 'Case ' . $case['id'],
            'currentPage' => 'cases',
            'case'        => $case,
        ]);
    }

    public function settings(): string
    {
        return $this->render('settings', [
            'pageTitle'   => 'Settings',
            'currentPage' => 'settings',
            'roles'       => CopyrightMockData::roles(),
            'permissions' => CopyrightMockData::permissionMatrix(),
        ]);
    }
}
