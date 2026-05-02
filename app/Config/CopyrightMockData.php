<?php

namespace App\Config;

/**
 * Static sample data for the Copyright Management mockup (no database).
 * TODO: Replace static arrays with Model queries + aggregation services.
 */
class CopyrightMockData
{
    /** @var list<array<string, mixed>>|null */
    private static ?array $worksCache = null;

    /** Work titles — fixed catalog of 50 items for the prototype. */
    private const WORK_TITLES = [
        'Aurora Fields — Stock Photo Pack',
        'Midnight Choir — Master Recording',
        'Meridian SaaS — Onboarding Video',
        'Policy Handbook 2026 (Internal)',
        'LedgerFlow — Mobile App UI',
        'Harborline Logo System',
        'Copyright Law for Creators (Course)',
        'Neon District — Loop Pack',
        'Patient Education Brochure Set',
        'Summit Keynote Slides 2026',
        'Coastal Timelapse Reel',
        'Podcast Intro Theme',
        'Product Demo — 4K Cut',
        'Annual Security Whitepaper',
        'Design System Tokens (Figma)',
        'Iconography Pack v3',
        'Music Licensing 101 (Course)',
        'Brand Voice Guidelines PDF',
        'Retail Window Campaign Photos',
        'ASMR Forest — Ambient Album',
        'Customer Story — Case Film',
        'API Developer Guide',
        'Mobile Checkout Flow — Prototype',
        'Spring Lookbook 2026',
        'Typography Specimen Book',
        'Beat Making Workshop (Course)',
        'Drone Survey — Construction Site',
        'Radio Spot — 30s Mix',
        'Training Series — Module 1–6',
        'Open Source CLI Tool',
        'Wayfinding Signage System',
        'UX Research Report Q1',
        'Jazz Quartet — Live Session',
        'Motion Pack — Lower Thirds',
        'Employee Handbook (EU)',
        'Component Library (React)',
        'Packaging Dieline Artwork',
        'SEO Playbook (Text)',
        'Synthwave Singles — EP',
        'Conference Opening Montage',
        'Firmware Update Notes',
        'Ceramic Tableware — Design Files',
        'Data Privacy Notice Templates',
        'Orchestral Stings Library',
        'Lifestyle Shoot — Spring',
        'Screencast Tutorial Series',
        'Infographic Series — Finance',
        'Mobile Game HUD Assets',
        'Wellness App Soundscapes',
        'Corporate Photobank 2026',
    ];

    private const WORK_TYPES = ['Image', 'Audio', 'Video', 'Text', 'Software', 'Design', 'Course'];

    private const CREATORS = [
        'Jamie Chen', 'Riley Ortiz', 'Sam Okonkwo', 'Morgan Blake', 'Taylor Singh',
        'Jordan Lee', 'Casey Nguyen', 'Alex Morgan', 'Priya Desai', 'Chris Ward',
        'Emery Fox', 'Logan Park', 'Avery Kim', 'Quinn Rivera', 'Sky Patel',
    ];

    private const OWNERS = [
        'Studio North LLC', 'Echo Lane Music', 'Pixel Harbor Inc.', 'Meridian Holdings',
        'Launchpad SaaS Ltd.', 'Harborline Creative Co.', 'Northwind Education', 'Brightfield Media',
        'Copper Kettle Audio', 'Atlas Legal Group', 'Silverline Design', 'Kite Analytics',
    ];

    private const STATUSES = ['Registered', 'Pending review', 'Registered', 'Registered', 'Under audit'];

    private const RISKS = ['Low', 'Medium', 'High'];

    public static function mockUser(): array
    {
        return [
            'name'  => 'Alex Morgan',
            'email' => 'alex.morgan@example.com',
            'role'  => 'Copyright Manager',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function works(): array
    {
        if (self::$worksCache !== null) {
            return self::$worksCache;
        }

        $rows = [];

        foreach (self::WORK_TITLES as $i => $title) {
            $n        = $i + 1;
            $id       = sprintf('WRK-%03d', $n);
            $type     = self::WORK_TYPES[$i % count(self::WORK_TYPES)];
            $creator  = self::CREATORS[$i % count(self::CREATORS)];
            $owner    = self::OWNERS[$i % count(self::OWNERS)];
            $status   = self::STATUSES[$i % count(self::STATUSES)];
            $risk     = self::RISKS[$i % count(self::RISKS)];

            // Spread registration dates across several months (deterministic offsets).
            $regDate = date('Y-m-d', strtotime('2025-09-01 +' . ($i * 11 + ($i % 7)) . ' days'));

            $licenseCount = ($i % 5) + ($i % 3);
            $lastUpdated  = date('Y-m-d', strtotime($regDate . ' +' . (10 + ($i % 40)) . ' days'));

            $rows[] = [
                'work_id'           => $id,
                'title'             => $title,
                'type'              => $type,
                'creator'           => $creator,
                'owner'             => $owner,
                'registration_date' => $regDate,
                'copyright_status'  => $status,
                'license_count'     => $licenseCount,
                'risk_level'        => $risk,
                'last_updated'      => $lastUpdated,
                // Legacy keys used by some views — keep aligned for one source of truth.
                'id'         => $id,
                'registered' => $regDate,
                'status'     => $status,
                'territory'  => ['Worldwide', 'North America', 'EU + UK', 'APAC'][$i % 4],
            ];
        }

        self::$worksCache = $rows;

        return self::$worksCache;
    }

    public static function workById(string $id): ?array
    {
        foreach (self::works() as $w) {
            if (($w['work_id'] ?? $w['id']) === $id) {
                $suffix = substr(str_replace('-', '', (string) $id), -5);

                return $w + [
                    'description' => 'Prototype description. Replace with catalog metadata from the database.',
                    'creators'    => array_values(array_unique([$w['creator'], self::CREATORS[($w['license_count'] ?? 1) % count(self::CREATORS)]])),
                    'identifiers' => ['Internal ref: CM-' . $suffix],
                ];
            }
        }

        return null;
    }

    /**
     * Dashboard KPI cards — derived from static works + license/case fixtures.
     * TODO: Replace with aggregated queries (counts, sums, date windows).
     */
    public static function dashboardStats(): array
    {
        $works    = self::works();
        $licenses = self::licenses();
        $cases    = self::infringementCases();

        $totalWorks = count($works);
        $activeLic  = count(array_filter($licenses, static fn ($l) => ($l['status'] ?? '') === 'Active'));
        $openCases  = count(array_filter($cases, static fn ($c) => ($c['status'] ?? '') !== 'Resolved'));

        $monthly    = self::licenseRevenueMonthly();
        $lastRev    = $monthly !== [] ? end($monthly) : null;
        $revAmount  = is_array($lastRev) ? (int) ($lastRev['amount'] ?? 0) : 0;
        $revLabel   = is_array($lastRev) ? (string) ($lastRev['month'] ?? 'Current') : 'Current';

        $thisMonth = date('Y-m');
        $registeredThisMonth = count(array_filter($works, static fn ($w) => str_starts_with($w['registration_date'], $thisMonth)));

        // KPI order: Total Works → Active Licenses → Monthly Revenue → Open Cases
        return [
            [
                'label' => 'Total Works',
                'value' => (string) $totalWorks,
                'hint'  => '+' . $registeredThisMonth . ' registered this month (mock)',
                'kpi'   => 'works',
            ],
            [
                'label' => 'Active Licenses',
                'value' => (string) $activeLic,
                'hint'  => '3 expiring within 90 days (sample)',
                'kpi'   => 'licenses',
            ],
            [
                'label' => 'Monthly Revenue',
                'value' => '$' . number_format($revAmount),
                'hint'  => 'Latest period: ' . $revLabel . ' (mock fixture)',
                'kpi'   => 'revenue',
            ],
            [
                'label' => 'Open Cases',
                'value' => (string) $openCases,
                'hint'  => '2 high priority in mock pipeline',
                'kpi'   => 'cases',
            ],
        ];
    }

    public static function recentActivity(): array
    {
        $w = self::works();
        $licenses = self::licenses();
        $licSample = $licenses[1]['id'] ?? 'LIC-2025-014';

        return [
            ['time' => '12m ago', 'type' => 'work', 'text' => 'New work registered: “' . $w[6]['title'] . '”'],
            ['time' => '28m ago', 'type' => 'license', 'text' => 'License issued: ' . $licSample . ' for “' . $w[4]['title'] . '”'],
            ['time' => '1h ago', 'type' => 'case', 'text' => 'Case IC-019 created — UI kit marketplace'],
            ['time' => '2h ago', 'type' => 'license', 'text' => 'License renewed for “' . $w[0]['title'] . '”'],
            ['time' => '3h ago', 'type' => 'usage', 'text' => 'Usage report approved — Q1 streaming bundle'],
            ['time' => '5h ago', 'type' => 'work', 'text' => 'Metadata updated: “' . $w[11]['title'] . '”'],
            ['time' => 'Yesterday', 'type' => 'case', 'text' => 'Case IC-014 moved to mediation'],
            ['time' => 'Yesterday', 'type' => 'license', 'text' => 'Enterprise license terms accepted — ' . ($licenses[4]['licensee'] ?? 'Partner')],
            ['time' => '2 days ago', 'type' => 'work', 'text' => 'New work registered: “' . $w[19]['title'] . '”'],
            ['time' => '2 days ago', 'type' => 'ownership', 'text' => 'Ownership updated for “' . $w[14]['title'] . '”'],
            ['time' => '3 days ago', 'type' => 'monitoring', 'text' => 'Scan completed — 14 potential matches flagged'],
            ['time' => '4 days ago', 'type' => 'case', 'text' => 'Case IC-021 escalated to legal review'],
            ['time' => '5 days ago', 'type' => 'license', 'text' => 'Broadcast license activated — “' . $w[12]['title'] . '”'],
            ['time' => '6 days ago', 'type' => 'work', 'text' => 'Draft archived: legacy pitch deck (mock)'],
        ];
    }

    /**
     * Chart axis labels (last 6 full months ending current month — static for prototype).
     * TODO: Drive from reporting period parameter.
     *
     * @return list<string>
     */
    public static function chartMonthLabels(): array
    {
        return ['Dec 2025', 'Jan 2026', 'Feb 2026', 'Mar 2026', 'Apr 2026', 'May 2026'];
    }

    /**
     * Monthly counts of new registrations (static series aligned to chart labels).
     * TODO: GROUP BY DATE_TRUNC('month', registration_date) from works table.
     *
     * @return list<int>
     */
    public static function registeredWorksMonthly(): array
    {
        return [4, 6, 9, 11, 8, 12];
    }

    /**
     * Active licenses end-of-month snapshot (mock).
     * TODO: Snapshot table or derived from license status history.
     *
     * @return list<int>
     */
    public static function activeLicensesMonthly(): array
    {
        return [28, 31, 34, 38, 40, 44];
    }

    /**
     * Infringement cases detected vs resolved per month (mock).
     * TODO: Case events table with type detected|resolved.
     *
     * @return array{detected: list<int>, resolved: list<int>}
     */
    public static function infringementMonthly(): array
    {
        return [
            'detected' => [3, 5, 4, 6, 5, 4],
            'resolved' => [2, 2, 3, 4, 3, 5],
        ];
    }

    /**
     * License revenue in USD (whole dollars) per month (mock).
     * TODO: Royalty / invoice fact table.
     *
     * @return list<array{month: string, amount: int}>
     */
    public static function licenseRevenueMonthly(): array
    {
        $months = self::chartMonthLabels();
        $amounts = [42000, 45500, 48200, 51000, 53800, 56200];

        $out = [];
        foreach ($months as $i => $m) {
            $out[] = ['month' => $m, 'amount' => $amounts[$i]];
        }

        return $out;
    }

    /**
     * @return list<array<string, string>>
     */
    public static function ownershipRows(): array
    {
        $w = self::works();

        return [
            ['work_id' => $w[0]['work_id'], 'work_title' => $w[0]['title'], 'owner' => $w[0]['owner'], 'share' => '100%', 'since' => $w[0]['registration_date']],
            ['work_id' => $w[1]['work_id'], 'work_title' => $w[1]['title'], 'owner' => $w[1]['owner'], 'share' => '60%', 'since' => $w[1]['registration_date']],
            ['work_id' => $w[1]['work_id'], 'work_title' => $w[1]['title'], 'owner' => 'Producer Collective', 'share' => '40%', 'since' => $w[1]['registration_date']],
            ['work_id' => $w[2]['work_id'], 'work_title' => $w[2]['title'], 'owner' => $w[2]['owner'], 'share' => '100%', 'since' => $w[2]['registration_date']],
            ['work_id' => $w[5]['work_id'], 'work_title' => $w[5]['title'], 'owner' => $w[5]['owner'], 'share' => '55%', 'since' => $w[5]['registration_date']],
            ['work_id' => $w[5]['work_id'], 'work_title' => $w[5]['title'], 'owner' => 'Partner Studio GmbH', 'share' => '45%', 'since' => $w[5]['registration_date']],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public static function licenses(): array
    {
        $w = self::works();

        return [
            [
                'id'         => 'LIC-2024-089',
                'work_id'    => $w[0]['work_id'],
                'work_title' => $w[0]['title'],
                'licensee'   => 'Metro Media Group',
                'type'       => 'Non-exclusive',
                'status'     => 'Active',
                'expires'    => '2026-06-30',
            ],
            [
                'id'         => 'LIC-2025-014',
                'work_id'    => $w[4]['work_id'],
                'work_title' => $w[4]['title'],
                'licensee'   => 'Launchpad SaaS Ltd.',
                'type'       => 'Enterprise',
                'status'     => 'Active',
                'expires'    => '2027-01-15',
            ],
            [
                'id'         => 'LIC-2023-201',
                'work_id'    => $w[3]['work_id'],
                'work_title' => $w[3]['title'],
                'licensee'   => 'Meridian Holdings',
                'type'       => 'Internal use',
                'status'     => 'Expired',
                'expires'    => '2025-01-01',
            ],
            [
                'id'         => 'LIC-2026-031',
                'work_id'    => $w[12]['work_id'],
                'work_title' => $w[12]['title'],
                'licensee'   => 'Brightfield Media',
                'type'       => 'Broadcast',
                'status'     => 'Active',
                'expires'    => '2026-12-31',
            ],
            [
                'id'         => 'LIC-2025-188',
                'work_id'    => $w[22]['work_id'],
                'work_title' => $w[22]['title'],
                'licensee'   => 'Kite Analytics',
                'type'       => 'OEM',
                'status'     => 'Active',
                'expires'    => '2026-09-01',
            ],
        ];
    }

    public static function licenseDetail(string $id): ?array
    {
        foreach (self::licenses() as $row) {
            if ($row['id'] === $id) {
                return $row + [
                    'royalty'   => '12% net receipts',
                    'territory' => 'United States',
                    'channels'  => 'Web, print, social paid ads',
                    'notes'     => 'Mock renewal window: 90 days before expiry.',
                ];
            }
        }

        return null;
    }

    /**
     * @return list<array<string, string>>
     */
    public static function usageReportRows(): array
    {
        $w = self::works();

        return [
            ['period' => '2026 Q1', 'work' => $w[0]['title'], 'channel' => 'Web', 'impressions' => '1.2M', 'revenue' => '$18,400'],
            ['period' => '2026 Q1', 'work' => $w[4]['title'], 'channel' => 'SaaS embed', 'impressions' => '—', 'revenue' => '$42,000'],
            ['period' => '2025 Q4', 'work' => $w[3]['title'], 'channel' => 'Print + PDF', 'impressions' => '85k', 'revenue' => '$9,200'],
            ['period' => '2026 Q1', 'work' => $w[11]['title'], 'channel' => 'Streaming', 'impressions' => '640k', 'revenue' => '$22,900'],
        ];
    }

    /**
     * @return list<array<string, string>>
     */
    public static function infringementCases(): array
    {
        $w = self::works();

        return [
            [
                'id'       => 'IC-014',
                'work_id'  => $w[0]['work_id'],
                'title'    => 'Unauthorized billboard use',
                'status'   => 'Mediation',
                'opened'   => '2025-09-12',
                'severity' => 'High',
            ],
            [
                'id'       => 'IC-019',
                'work_id'  => $w[4]['work_id'],
                'title'    => 'UI kit resold on marketplace',
                'status'   => 'Investigation',
                'opened'   => '2026-01-20',
                'severity' => 'High',
            ],
            [
                'id'       => 'IC-021',
                'work_id'  => $w[1]['work_id'],
                'title'    => 'Uncleared sample in third-party remix',
                'status'   => 'Monitoring',
                'opened'   => '2026-02-02',
                'severity' => 'Medium',
            ],
            [
                'id'       => 'IC-024',
                'work_id'  => $w[16]['work_id'],
                'title'    => 'Course videos scraped to ad-supported site',
                'status'   => 'Resolved',
                'opened'   => '2025-11-05',
                'severity' => 'Medium',
            ],
        ];
    }

    public static function caseDetail(string $id): ?array
    {
        foreach (self::infringementCases() as $row) {
            if ($row['id'] === $id) {
                $milestones = match ($row['id']) {
                    'IC-024' => [
                        ['date' => $row['opened'], 'label' => 'Case opened'],
                        ['date' => '2025-11-18', 'label' => 'Takedown request sent'],
                        ['date' => '2026-01-08', 'label' => 'Resolved — content removed'],
                    ],
                    default => [
                        ['date' => $row['opened'], 'label' => 'Case opened'],
                        ['date' => '2025-10-01', 'label' => 'Evidence packet assembled'],
                        ['date' => '2026-02-10', 'label' => 'Mediation scheduled'],
                    ],
                };

                return $row + [
                    'assignee'       => 'Jordan Lee',
                    'jurisdiction'   => 'California, USA',
                    'summary'        => 'Mock summary: alleged use outside licensed territory and channel.',
                    'milestones'     => $milestones,
                ];
            }
        }

        return null;
    }

    /**
     * @return list<array{name: string, users: string, description: string}>
     */
    public static function roles(): array
    {
        return [
            ['name' => 'Admin', 'users' => '4', 'description' => 'Full system access (mock).'],
            ['name' => 'Copyright Manager', 'users' => '9', 'description' => 'Works, licenses, cases.'],
            ['name' => 'Legal Counsel', 'users' => '3', 'description' => 'Read + case workflow.'],
            ['name' => 'Viewer', 'users' => '12', 'description' => 'Read-only dashboards.'],
        ];
    }

    /**
     * Mock permission matrix for Settings UI (static).
     * TODO: Load from RBAC / policy store.
     *
     * @return list<array{module: string, admin: bool, manager: bool, legal: bool, viewer: bool}>
     */
    public static function permissionMatrix(): array
    {
        return [
            ['module' => 'Assets & catalog', 'admin' => true, 'manager' => true, 'legal' => true, 'viewer' => true],
            ['module' => 'License issuance', 'admin' => true, 'manager' => true, 'legal' => false, 'viewer' => false],
            ['module' => 'Financial reports', 'admin' => true, 'manager' => true, 'legal' => false, 'viewer' => false],
            ['module' => 'Case workflow', 'admin' => true, 'manager' => true, 'legal' => true, 'viewer' => false],
            ['module' => 'Monitoring & takedowns', 'admin' => true, 'manager' => true, 'legal' => true, 'viewer' => false],
            ['module' => 'Org settings', 'admin' => true, 'manager' => false, 'legal' => false, 'viewer' => false],
        ];
    }

    /**
     * Infringement / web monitoring scan rows (mock).
     * TODO: Replace with scan job results from a service.
     *
     * @return list<array<string, string>>
     */
    public static function monitoringScans(): array
    {
        $w = self::works();

        return [
            [
                'scan_id'   => 'SCN-2026-0512',
                'started'   => '2026-05-02 09:15',
                'status'    => 'Complete',
                'matches'   => '14',
                'severity'  => 'Medium',
                'top_hit'   => '“' . $w[0]['title'] . '” — 6 domains',
            ],
            [
                'scan_id'   => 'SCN-2026-0509',
                'started'   => '2026-04-29 22:00',
                'status'    => 'Complete',
                'matches'   => '22',
                'severity'  => 'High',
                'top_hit'   => '“' . $w[4]['title'] . '” — marketplace listings',
            ],
            [
                'scan_id'   => 'SCN-2026-0504',
                'started'   => '2026-04-27 06:30',
                'status'    => 'Running',
                'matches'   => '—',
                'severity'  => '—',
                'top_hit'   => 'Social + video platforms (in progress)',
            ],
            [
                'scan_id'   => 'SCN-2026-0498',
                'started'   => '2026-04-21 11:40',
                'status'    => 'Complete',
                'matches'   => '9',
                'severity'  => 'Low',
                'top_hit'   => '“' . $w[16]['title'] . '” — education mirrors',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function licensesForWork(string $workId): array
    {
        return array_values(array_filter(self::licenses(), static fn ($l) => ($l['work_id'] ?? '') === $workId));
    }

    /**
     * @return list<array<string, string>>
     */
    public static function usageRowsForWork(string $workTitle): array
    {
        return array_values(array_filter(self::usageReportRows(), static fn ($r) => ($r['work'] ?? '') === $workTitle));
    }
}
