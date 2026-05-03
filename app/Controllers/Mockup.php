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
        $mockUser = CopyrightMockData::mockUser();

        $defaults = [
            'pageTitle'    => 'Copyright Management',
            'currentPage'  => '',
            'currentUser'  => [
                'name' => $mockUser['name'],
                'role' => $mockUser['role'],
            ],
            'useAuthLogout'=> false,
            'appCrumb'     => 'Copyright Management · UI mockup',
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
        $wid  = null;

        if ($work !== null) {
            $wid = (string) ($work['work_id'] ?? $work['id'] ?? '');
        } elseif (ctype_digit($id)) {
            $bundle = $this->loadWorkBundleFromDatabase((int) $id);
            if ($bundle === null) {
                throw PageNotFoundException::forPageNotFound();
            }
            $work           = $bundle['work'];
            $workLicenses   = $bundle['workLicenses'];
            $workUsageRows  = $bundle['workUsageRows'];
            $ownershipRows  = $bundle['ownershipRows'];

            return $this->render('work_detail', [
                'pageTitle'     => $work['title'],
                'currentPage'   => 'assets',
                'work'          => $work,
                'workLicenses'  => $workLicenses,
                'workUsageRows' => $workUsageRows,
                'ownershipRows' => $ownershipRows,
            ]);
        } else {
            throw PageNotFoundException::forPageNotFound();
        }

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

    /**
     * @return array{work: array<string, mixed>, workLicenses: list<array<string, string>>, workUsageRows: list<array<string, string>>, ownershipRows: list<array<string, string>>}|null
     */
    private function loadWorkBundleFromDatabase(int $workId): ?array
    {
        $db = db_connect();

        $row = $db->table('works')->where('id', $workId)->get()->getRowArray();
        if ($row === null) {
            return null;
        }

        $reg = $row['registered_at'] ?? null;
        $regDisp = $reg ? date('M j, Y', strtotime((string) $reg)) : '—';
        $upd = $row['updated_at'] ?? null;
        $updDisp = $upd ? date('M j, Y', strtotime((string) $upd)) : '—';

        $ownerRow = $db->table('owners')->where('work_id', $workId)->orderBy('id', 'ASC')->get()->getRowArray();
        $ownerName = $ownerRow !== null ? (string) $ownerRow['legal_name'] : '—';

        $licenseCount = $db->table('licenses')->where('work_id', $workId)->countAllResults();

        $licRows = $db->table('licenses l')
            ->select('l.id, l.status, l.ends_on, le.name AS licensee_name')
            ->join('licensees le', 'le.id = l.licensee_id', 'left')
            ->where('l.work_id', $workId)
            ->get()
            ->getResultArray();

        $workLicenses = [];
        foreach ($licRows as $lr) {
            $st = (string) ($lr['status'] ?? '');
            $workLicenses[] = [
                'id'        => 'LIC-' . $lr['id'],
                'licensee'  => (string) ($lr['licensee_name'] ?? '—'),
                'type'      => '—',
                'status'    => $st !== '' ? ucfirst($st) : '—',
                'expires'   => $lr['ends_on'] !== null ? (string) $lr['ends_on'] : '—',
            ];
        }

        $ownRows = $db->table('owners')->where('work_id', $workId)->orderBy('id', 'ASC')->get()->getResultArray();
        $ownershipRows = [];
        foreach ($ownRows as $o) {
            $created = $o['created_at'] ?? null;
            $ownershipRows[] = [
                'owner' => (string) $o['legal_name'],
                'share' => '—',
                'since' => $created ? date('Y-m-d', strtotime((string) $created)) : '—',
            ];
        }

        $licenseIds = array_column($licRows, 'id');
        $workUsageRows = [];
        if ($licenseIds !== [] && $db->tableExists('license_usage_snapshots')) {
            $usageDbRows = $db->table('license_usage_snapshots')
                ->whereIn('license_id', $licenseIds)
                ->orderBy('period_start', 'DESC')
                ->get()
                ->getResultArray();
            foreach ($usageDbRows as $u) {
                $rev = $u['revenue_amount'] ?? null;
                $workUsageRows[] = [
                    'period'       => ($u['period_start'] ?? '') . ' – ' . ($u['period_end'] ?? ''),
                    'channel'      => '—',
                    'impressions'  => $u['usage_units'] !== null ? (string) $u['usage_units'] : '—',
                    'revenue'      => $rev !== null ? '$' . number_format((float) $rev, 2) : '—',
                ];
            }
        }

        $work = [
            'work_id'             => (string) $workId,
            'id'                  => (string) $workId,
            'title'               => (string) $row['title'],
            'type'                => '—',
            'creator'             => '—',
            'owner'               => $ownerName,
            'copyright_status'    => (string) ($row['copyright_status'] ?? 'draft'),
            'registration_date'   => $regDisp,
            'territory'           => '—',
            'license_count'       => $licenseCount,
            'risk_level'          => 'Low',
            'last_updated'        => $updDisp,
            'description'         => 'Catalog record from the database. Rich metadata will expand as modules connect.',
            'creators'            => [],
            'identifiers'         => ['Work #' . $workId],
        ];

        return [
            'work'           => $work,
            'workLicenses'   => $workLicenses,
            'workUsageRows'  => $workUsageRows,
            'ownershipRows'  => $ownershipRows,
        ];
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
