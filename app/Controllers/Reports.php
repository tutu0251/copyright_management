<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\InfringementCaseModel;
use App\Models\LicenseModel;
use App\Models\UsageReportModel;
use App\Models\WorkModel;
use CodeIgniter\HTTP\ResponseInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

class Reports extends BaseController
{
    protected $helpers = ['form', 'url', 'auth', 'permission', 'nav'];

    /**
     * @return array{
     *   preset: string,
     *   date_from: string,
     *   date_to: string,
     *   range_start: string,
     *   range_end: string,
     *   work_type: string,
     *   license_status: string,
     *   case_status: string,
     *   query: array<string, string>
     * }
     */
    private function parseFilters(): array
    {
        $preset = strtolower(trim((string) ($this->request->getGet('preset') ?? '30')));
        if (! in_array($preset, ['7', '30', '90', 'custom'], true)) {
            $preset = '30';
        }

        $from = trim((string) ($this->request->getGet('date_from') ?? ''));
        $to   = trim((string) ($this->request->getGet('date_to') ?? ''));

        if ($preset === 'custom') {
            if ($from === '' || $to === '') {
                $preset = '30';
            } else {
                $tsFrom = strtotime($from . ' 00:00:00');
                $tsTo   = strtotime($to . ' 23:59:59');
                if ($tsFrom === false || $tsTo === false || $tsFrom > $tsTo) {
                    $preset = '30';
                } elseif (($tsTo - $tsFrom) / 86400 > 366) {
                    $preset = '30';
                }
            }
        }

        if ($preset !== 'custom') {
            $days   = (int) $preset;
            $endDay = date('Y-m-d');
            $from   = date('Y-m-d', strtotime('-' . $days . ' days'));
            $to     = $endDay;
        }

        $rangeStart = $from . ' 00:00:00';
        $rangeEnd   = $to . ' 23:59:59';

        $workModel = model(WorkModel::class);
        $types     = $workModel->distinctWorkTypes();
        $workType  = trim((string) ($this->request->getGet('work_type') ?? ''));
        if ($workType !== '' && ! in_array($workType, $types, true)) {
            $workType = '';
        }

        $licenseStatus = trim((string) ($this->request->getGet('license_status') ?? ''));
        if ($licenseStatus !== '' && ! in_array($licenseStatus, LicenseModel::LICENSE_STATUSES, true)) {
            $licenseStatus = '';
        }

        $caseStatus = trim((string) ($this->request->getGet('case_status') ?? ''));
        if ($caseStatus !== '' && ! in_array($caseStatus, InfringementCaseModel::ALL_STATUSES, true)) {
            $caseStatus = '';
        }

        $query = array_filter([
            'preset'          => $preset,
            'date_from'       => $preset === 'custom' ? $from : '',
            'date_to'         => $preset === 'custom' ? $to : '',
            'work_type'       => $workType,
            'license_status'  => $licenseStatus,
            'case_status'     => $caseStatus,
        ], static fn ($v) => $v !== null && $v !== '');

        if ($preset !== 'custom') {
            unset($query['date_from'], $query['date_to']);
        }

        return [
            'preset'          => $preset,
            'date_from'       => $from,
            'date_to'         => $to,
            'range_start'     => $rangeStart,
            'range_end'       => $rangeEnd,
            'work_type'       => $workType,
            'license_status'  => $licenseStatus,
            'case_status'     => $caseStatus,
            'query'           => $query,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'     => 'Reports',
            'currentPage'   => 'reports',
            'currentUser'   => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'           => copyright_nav_items(),
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => 'Copyright Management · Analytics',
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    private function exportQueryString(array $filters, string $report): string
    {
        $q = $filters['query'];
        $q['report'] = $report;

        return http_build_query($q);
    }

    public function index(): string
    {
        $f = $this->parseFilters();

        $workModel   = model(WorkModel::class);
        $licenseModel = model(LicenseModel::class);
        $usageReady  = UsageReportModel::monitoringSchemaReady();
        $casesReady  = InfringementCaseModel::schemaReady();
        $auditReady  = AuditLogModel::schemaReady();

        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;

        $worksTotal   = $workModel->countCatalog($wt);
        $worksCreated = $workModel->countCreatedBetween($f['range_start'], $f['range_end'], $wt);

        $licSnap = $licenseModel->reportStatusSnapshot($wt, $f['license_status'] !== '' ? $f['license_status'] : null);
        $licRev  = $licenseModel->revenueSnapshot($wt, $f['license_status'] !== '' ? $f['license_status'] : null);

        $usageCount = $usageReady
            ? model(UsageReportModel::class)->countDetectionsBetween($f['range_start'], $f['range_end'], $wt)
            : 0;

        $casesTotal = $casesReady
            ? model(InfringementCaseModel::class)->countFiltered($f['case_status'] !== '' ? $f['case_status'] : null, $f['range_start'], $f['range_end'])
            : 0;

        $auditCount = $auditReady
            ? (int) model(AuditLogModel::class)->builder()
                ->where('created_at >=', $f['range_start'])
                ->where('created_at <=', $f['range_end'])
                ->countAllResults()
            : 0;

        $monthsBack = min(12, max(2, (int) ceil((strtotime($f['range_end']) - strtotime($f['range_start'])) / 86400 / 30) + 1));
        $monthsBack = max(2, min(12, $monthsBack));
        $worksByMonth = $workModel->countNewWorksByMonth($monthsBack, $wt);
        $labels       = [];
        $series       = [];
        for ($i = $monthsBack - 1; $i >= 0; $i--) {
            $ym       = date('Y-m', strtotime('-' . $i . ' months'));
            $labels[] = date('M Y', strtotime($ym . '-01'));
            $series[] = (int) ($worksByMonth[$ym] ?? 0);
        }

        $exportBase = $this->exportQueryString($f, 'summary');

        return $this->layout('reports/index', [
            'pageTitle'      => 'Reports',
            'filters'        => $f,
            'workTypes'      => $workModel->distinctWorkTypes(),
            'worksTotal'     => $worksTotal,
            'worksCreated'   => $worksCreated,
            'licSnap'        => $licSnap,
            'licRev'         => $licRev,
            'usageCount'     => $usageCount,
            'casesTotal'     => $casesTotal,
            'auditCount'     => $auditCount,
            'usageReady'     => $usageReady,
            'casesReady'     => $casesReady,
            'auditReady'     => $auditReady,
            'useCharts'      => true,
            'chartPayload'   => [
                'labels'   => $labels,
                'overview' => $series,
            ],
            'exportQuery'    => $exportBase,
        ]);
    }

    public function works(): string
    {
        $f = $this->parseFilters();
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;

        $workModel = model(WorkModel::class);

        $total        = $workModel->countCatalog($wt);
        $createdIn    = $workModel->countCreatedBetween($f['range_start'], $f['range_end'], $wt);
        $byType       = $workModel->countsByWorkTypeWindow($f['range_start'], $f['range_end'], $wt);
        $byOwner      = $workModel->topOwnersByLinkedWorks(25, $f['range_start'], $f['range_end'], $wt);
        $dailyCreated = $workModel->countCreatedByDayBetween($f['date_from'], $f['date_to'], $wt);

        $labels = array_column($dailyCreated, 'd');
        $counts = array_column($dailyCreated, 'c');

        $typeLabels = array_map(static fn ($r) => (string) ($r['work_type'] ?? ''), $byType);
        $typeCounts = array_map(static fn ($r) => (int) ($r['c'] ?? 0), $byType);

        return $this->layout('reports/works', [
            'pageTitle'    => 'Works report',
            'filters'      => $f,
            'workTypes'    => $workModel->distinctWorkTypes(),
            'total'        => $total,
            'createdIn'    => $createdIn,
            'byType'       => $byType,
            'byOwner'      => $byOwner,
            'useCharts'    => true,
            'chartPayload' => [
                'lineLabels' => $labels,
                'lineData'   => $counts,
                'pieLabels'  => $typeLabels,
                'pieData'    => $typeCounts,
                'barLabels'  => array_map(static fn ($o) => (string) ($o['name'] ?? ''), $byOwner),
                'barData'    => array_map(static fn ($o) => (int) ($o['work_count'] ?? 0), $byOwner),
            ],
            'exportQuery'  => $this->exportQueryString($f, 'works'),
        ]);
    }

    public function licenses(): string
    {
        $f = $this->parseFilters();
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;
        $ls = $f['license_status'] !== '' ? $f['license_status'] : null;

        $licenseModel = model(LicenseModel::class);

        $snap    = $licenseModel->reportStatusSnapshot($wt, $ls);
        $revenue = $licenseModel->revenueSnapshot($wt, $ls);
        $byPay   = $licenseModel->countsByPaymentStatus($wt, $ls);
        $daily   = $licenseModel->countCreatedByDayBetween($f['date_from'], $f['date_to'], $wt, $ls);

        $pieLabels = ['Active (in force)', 'Expired', 'Expiring ≤30d'];
        $pieData   = [$snap['active'], $snap['expired'], $snap['expiring_30']];

        $payLabels = [];
        $payData   = [];
        foreach (LicenseModel::PAYMENT_STATUSES as $p) {
            $payLabels[] = LicenseModel::paymentLabel($p);
            $payData[]   = (int) ($byPay[$p] ?? 0);
        }

        return $this->layout('reports/licenses', [
            'pageTitle'    => 'License report',
            'filters'      => $f,
            'workTypes'    => model(WorkModel::class)->distinctWorkTypes(),
            'licenseStatuses' => LicenseModel::LICENSE_STATUSES,
            'snap'         => $snap,
            'revenue'      => $revenue,
            'byPay'        => $byPay,
            'daily'        => $daily,
            'useCharts'    => true,
            'chartPayload' => [
                'lineLabels' => array_column($daily, 'd'),
                'lineData'   => array_column($daily, 'c'),
                'pieLabels'  => $pieLabels,
                'pieData'    => $pieData,
                'barLabels'  => $payLabels,
                'barData'    => $payData,
            ],
            'exportQuery'  => $this->exportQueryString($f, 'licenses'),
        ]);
    }

    public function usage(): string
    {
        $f = $this->parseFilters();
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;

        $ready = UsageReportModel::monitoringSchemaReady();
        $usageModel = model(UsageReportModel::class);

        $total = $ready ? $usageModel->countDetectionsBetween($f['range_start'], $f['range_end'], $wt) : 0;
        $byUt  = $ready ? $usageModel->countsByUsageTypeBetween($f['range_start'], $f['range_end'], $wt) : [];
        $byDt  = $ready ? $usageModel->countsByDetectedTypeBetween($f['range_start'], $f['range_end'], $wt) : [];
        $daily = $ready ? $usageModel->countDetectionsByDayBetween($f['date_from'], $f['date_to'], $wt) : [];

        $utLabels = [];
        $utData   = [];
        foreach (UsageReportModel::USAGE_TYPES as $u) {
            $utLabels[] = UsageReportModel::usageTypeLabel($u);
            $utData[]   = (int) ($byUt[$u] ?? 0);
        }

        $dtLabels = [];
        $dtData   = [];
        foreach (UsageReportModel::DETECTED_TYPES as $d) {
            $dtLabels[] = UsageReportModel::detectedTypeLabel($d);
            $dtData[]   = (int) ($byDt[$d] ?? 0);
        }

        return $this->layout('reports/usage', [
            'pageTitle'    => 'Usage detections report',
            'filters'      => $f,
            'workTypes'    => model(WorkModel::class)->distinctWorkTypes(),
            'ready'        => $ready,
            'total'        => $total,
            'byUt'         => $byUt,
            'byDt'         => $byDt,
            'useCharts'    => $ready,
            'chartPayload' => [
                'lineLabels' => array_column($daily, 'd'),
                'lineData'   => array_column($daily, 'c'),
                'pieLabels'  => $utLabels,
                'pieData'    => $utData,
                'barLabels'  => $dtLabels,
                'barData'    => $dtData,
            ],
            'exportQuery'  => $this->exportQueryString($f, 'usage'),
        ]);
    }

    public function cases(): string
    {
        $f = $this->parseFilters();

        $ready = InfringementCaseModel::schemaReady();
        $caseModel = model(InfringementCaseModel::class);

        $totalInRange = $ready ? $caseModel->countFiltered($f['case_status'] !== '' ? $f['case_status'] : null, $f['range_start'], $f['range_end']) : 0;
        $openResolved = $ready ? $caseModel->countOpenVsResolvedSnapshot($f['case_status'] !== '' ? $f['case_status'] : null) : ['open' => 0, 'resolved' => 0];
        $byPri        = $ready ? $caseModel->countsByPriority($f['case_status'] !== '' ? $f['case_status'] : null) : [];
        $avgDays      = $ready ? $caseModel->averageResolutionDaysBetween($f['range_start'], $f['range_end']) : null;
        $dailyOpened  = $ready ? $caseModel->countOpenedByDayBetween($f['date_from'], $f['date_to']) : [];

        $priLabels = [];
        $priData   = [];
        foreach (InfringementCaseModel::PRIORITIES as $p) {
            $priLabels[] = InfringementCaseModel::priorityLabel($p);
            $priData[]   = (int) ($byPri[$p] ?? 0);
        }

        return $this->layout('reports/cases', [
            'pageTitle'     => 'Infringement report',
            'filters'       => $f,
            'workTypes'     => model(WorkModel::class)->distinctWorkTypes(),
            'caseStatuses'  => InfringementCaseModel::ALL_STATUSES,
            'ready'         => $ready,
            'totalInRange'  => $totalInRange,
            'openResolved'  => $openResolved,
            'byPri'         => $byPri,
            'avgDays'       => $avgDays,
            'useCharts'     => $ready,
            'chartPayload'  => [
                'lineLabels' => array_column($dailyOpened, 'd'),
                'lineData'   => array_column($dailyOpened, 'c'),
                'pieLabels'  => ['Open', 'Resolved'],
                'pieData'    => [$openResolved['open'], $openResolved['resolved']],
                'barLabels'  => $priLabels,
                'barData'    => $priData,
            ],
            'exportQuery'   => $this->exportQueryString($f, 'cases'),
        ]);
    }

    public function activity(): string
    {
        $f = $this->parseFilters();

        $ready = AuditLogModel::schemaReady();
        $audit   = model(AuditLogModel::class);

        $topUsers = $ready ? $audit->listUsersByActionCountBetween($f['range_start'], $f['range_end'], 25) : [];
        $daily    = $ready ? $audit->countActionsByDayBetween($f['date_from'], $f['date_to']) : [];
        $actions  = $ready ? $audit->topActionTypesBetween($f['range_start'], $f['range_end'], 15) : [];

        $userLabels = [];
        $userCounts = [];
        foreach ($topUsers as $u) {
            $nm = (string) ($u['display_name'] ?? '');
            if ($nm === '') {
                $nm = (string) ($u['email'] ?? 'User #' . ($u['user_id'] ?? ''));
            }
            $userLabels[] = $nm;
            $userCounts[] = (int) ($u['action_count'] ?? 0);
        }

        $actLabels = array_map(static fn ($r) => (string) ($r['action_type'] ?? ''), $actions);
        $actCounts = array_map(static fn ($r) => (int) ($r['c'] ?? 0), $actions);

        return $this->layout('reports/activity', [
            'pageTitle'    => 'User activity report',
            'filters'      => $f,
            'ready'        => $ready,
            'topUsers'     => $topUsers,
            'actions'      => $actions,
            'useCharts'    => $ready,
            'chartPayload' => [
                'lineLabels' => array_column($daily, 'd'),
                'lineData'   => array_column($daily, 'c'),
                'barLabels'  => $userLabels,
                'barData'    => $userCounts,
                'pieLabels'  => array_slice($actLabels, 0, 8),
                'pieData'    => array_slice($actCounts, 0, 8),
            ],
            'exportQuery'  => $this->exportQueryString($f, 'activity'),
        ]);
    }

    public function exportCsv(): ResponseInterface
    {
        $f = $this->parseFilters();
        $report = strtolower(trim((string) ($this->request->getGet('report') ?? 'summary')));
        if (! in_array($report, ['summary', 'works', 'licenses', 'usage', 'cases', 'activity'], true)) {
            $report = 'summary';
        }

        $fh = fopen('php://temp', 'r+');
        if ($fh === false) {
            return $this->response->setStatusCode(500)->setBody('Unable to build export.');
        }

        match ($report) {
            'works'     => $this->csvWorks($fh, $f),
            'licenses'  => $this->csvLicenses($fh, $f),
            'usage'     => $this->csvUsage($fh, $f),
            'cases'     => $this->csvCases($fh, $f),
            'activity'  => $this->csvActivity($fh, $f),
            default     => $this->csvSummary($fh, $f),
        };

        rewind($fh);
        $body = stream_get_contents($fh);
        fclose($fh);

        $fn = 'report-' . $report . '-' . date('Y-m-d') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fn . '"')
            ->setBody($body ?: '');
    }

    public function exportPdf(): ResponseInterface
    {
        $f = $this->parseFilters();
        $report = strtolower(trim((string) ($this->request->getGet('report') ?? 'summary')));
        if (! in_array($report, ['summary', 'works', 'licenses', 'usage', 'cases', 'activity'], true)) {
            $report = 'summary';
        }

        $html = match ($report) {
            'works'     => $this->pdfWorksHtml($f),
            'licenses'  => $this->pdfLicensesHtml($f),
            'usage'     => $this->pdfUsageHtml($f),
            'cases'     => $this->pdfCasesHtml($f),
            'activity'  => $this->pdfActivityHtml($f),
            default     => $this->pdfSummaryHtml($f),
        };

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fn = 'report-' . $report . '-' . date('Y-m-d') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fn . '"')
            ->setBody($dompdf->output());
    }

    /**
     * @param resource $fh
     */
    private function csvSummary($fh, array $f): void
    {
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;
        fputcsv($fh, ['Copyright Management — Summary report']);
        fputcsv($fh, ['Range', $f['date_from'] . ' to ' . $f['date_to']]);
        fputcsv($fh, ['Work type filter', $wt ?? '']);
        fputcsv($fh, ['License status filter', $f['license_status']]);
        fputcsv($fh, ['Case status filter', $f['case_status']]);
        fputcsv($fh, []);

        $wm = model(WorkModel::class);
        fputcsv($fh, ['Total works', (string) $wm->countCatalog($wt)]);
        fputcsv($fh, ['Works created in range', (string) $wm->countCreatedBetween($f['range_start'], $f['range_end'], $wt)]);

        $lm = model(LicenseModel::class);
        $snap = $lm->reportStatusSnapshot($wt, $f['license_status'] !== '' ? $f['license_status'] : null);
        $rev   = $lm->revenueSnapshot($wt, $f['license_status'] !== '' ? $f['license_status'] : null);
        fputcsv($fh, ['Active licenses', (string) $snap['active']]);
        fputcsv($fh, ['Expired licenses', (string) $snap['expired']]);
        fputcsv($fh, ['Expiring within 30 days', (string) $snap['expiring_30']]);
        fputcsv($fh, ['Revenue (paid fees sum)', (string) $rev['paid_sum']]);
        fputcsv($fh, ['Unpaid + partial fees sum', (string) $rev['unpaid_sum']]);

        if (UsageReportModel::monitoringSchemaReady()) {
            $uc = model(UsageReportModel::class)->countDetectionsBetween($f['range_start'], $f['range_end'], $wt);
            fputcsv($fh, ['Usage detections in range', (string) $uc]);
        }

        if (InfringementCaseModel::schemaReady()) {
            $cm = model(InfringementCaseModel::class);
            fputcsv($fh, ['Cases in range', (string) $cm->countFiltered($f['case_status'] !== '' ? $f['case_status'] : null, $f['range_start'], $f['range_end'])]);
        }

        if (AuditLogModel::schemaReady()) {
            $ac = (int) model(AuditLogModel::class)->builder()
                ->where('created_at >=', $f['range_start'])
                ->where('created_at <=', $f['range_end'])
                ->countAllResults();
            fputcsv($fh, ['Audit actions in range', (string) $ac]);
        }
    }

    /**
     * @param resource $fh
     */
    private function csvWorks($fh, array $f): void
    {
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;
        $wm = model(WorkModel::class);
        fputcsv($fh, ['Works by type', 'Count']);
        foreach ($wm->countsByWorkTypeWindow($f['range_start'], $f['range_end'], $wt) as $r) {
            fputcsv($fh, [(string) ($r['work_type'] ?? ''), (string) ($r['c'] ?? 0)]);
        }
        fputcsv($fh, []);
        fputcsv($fh, ['Owner', 'Linked works']);
        foreach ($wm->topOwnersByLinkedWorks(100, $f['range_start'], $f['range_end'], $wt) as $o) {
            fputcsv($fh, [(string) ($o['name'] ?? ''), (string) ($o['work_count'] ?? 0)]);
        }
    }

    /**
     * @param resource $fh
     */
    private function csvLicenses($fh, array $f): void
    {
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;
        $ls = $f['license_status'] !== '' ? $f['license_status'] : null;
        $lm = model(LicenseModel::class);
        fputcsv($fh, ['Metric', 'Value']);
        $snap = $lm->reportStatusSnapshot($wt, $ls);
        fputcsv($fh, ['Active', (string) $snap['active']]);
        fputcsv($fh, ['Expired', (string) $snap['expired']]);
        fputcsv($fh, ['Expiring 30d', (string) $snap['expiring_30']]);
        $rev = $lm->revenueSnapshot($wt, $ls);
        fputcsv($fh, ['Paid sum', (string) $rev['paid_sum']]);
        fputcsv($fh, ['Unpaid sum', (string) $rev['unpaid_sum']]);
        fputcsv($fh, []);
        fputcsv($fh, ['Payment status', 'Count']);
        foreach ($lm->countsByPaymentStatus($wt, $ls) as $k => $v) {
            fputcsv($fh, [$k, (string) $v]);
        }
    }

    /**
     * @param resource $fh
     */
    private function csvUsage($fh, array $f): void
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            fputcsv($fh, ['Usage monitoring schema not available']);

            return;
        }
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;
        $um = model(UsageReportModel::class);
        fputcsv($fh, ['Usage type', 'Count']);
        foreach ($um->countsByUsageTypeBetween($f['range_start'], $f['range_end'], $wt) as $k => $v) {
            fputcsv($fh, [$k, (string) $v]);
        }
        fputcsv($fh, []);
        fputcsv($fh, ['Source type', 'Count']);
        foreach ($um->countsByDetectedTypeBetween($f['range_start'], $f['range_end'], $wt) as $k => $v) {
            fputcsv($fh, [$k, (string) $v]);
        }
    }

    /**
     * @param resource $fh
     */
    private function csvCases($fh, array $f): void
    {
        if (! InfringementCaseModel::schemaReady()) {
            fputcsv($fh, ['Cases schema not available']);

            return;
        }
        $cm = model(InfringementCaseModel::class);
        $st = $f['case_status'] !== '' ? $f['case_status'] : null;
        fputcsv($fh, ['Open', (string) $cm->countOpenVsResolvedSnapshot($st)['open']]);
        fputcsv($fh, ['Resolved', (string) $cm->countOpenVsResolvedSnapshot($st)['resolved']]);
        fputcsv($fh, ['Cases opened in range', (string) $cm->countFiltered($st, $f['range_start'], $f['range_end'])]);
        fputcsv($fh, ['Avg resolution days (resolved in range)', (string) ($cm->averageResolutionDaysBetween($f['range_start'], $f['range_end']) ?? '')]);
        fputcsv($fh, []);
        fputcsv($fh, ['Priority', 'Count']);
        foreach ($cm->countsByPriority($st) as $k => $v) {
            fputcsv($fh, [$k, (string) $v]);
        }
    }

    /**
     * @param resource $fh
     */
    private function csvActivity($fh, array $f): void
    {
        if (! AuditLogModel::schemaReady()) {
            fputcsv($fh, ['Audit log not available']);

            return;
        }
        $am = model(AuditLogModel::class);
        fputcsv($fh, ['User', 'Email', 'Actions']);
        foreach ($am->listUsersByActionCountBetween($f['range_start'], $f['range_end'], 200) as $u) {
            fputcsv($fh, [
                (string) ($u['display_name'] ?? ''),
                (string) ($u['email'] ?? ''),
                (string) ($u['action_count'] ?? 0),
            ]);
        }
        fputcsv($fh, []);
        fputcsv($fh, ['Action type', 'Count']);
        foreach ($am->topActionTypesBetween($f['range_start'], $f['range_end'], 50) as $r) {
            fputcsv($fh, [(string) ($r['action_type'] ?? ''), (string) ($r['c'] ?? 0)]);
        }
    }

    private function pdfEscape(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function pdfSummaryHtml(array $f): string
    {
        $rows = [];
        $wt    = $f['work_type'] !== '' ? $f['work_type'] : null;
        $wm    = model(WorkModel::class);
        $rows[] = ['Total works', (string) $wm->countCatalog($wt)];
        $rows[] = ['Works created in range', (string) $wm->countCreatedBetween($f['range_start'], $f['range_end'], $wt)];
        $lm = model(LicenseModel::class);
        $snap = $lm->reportStatusSnapshot($wt, $f['license_status'] !== '' ? $f['license_status'] : null);
        $rev  = $lm->revenueSnapshot($wt, $f['license_status'] !== '' ? $f['license_status'] : null);
        $rows[] = ['Active licenses', (string) $snap['active']];
        $rows[] = ['Expired licenses', (string) $snap['expired']];
        $rows[] = ['Paid fees sum', (string) $rev['paid_sum']];
        $rows[] = ['Unpaid fees sum', (string) $rev['unpaid_sum']];

        return $this->pdfWrap('Summary report', $f, $rows);
    }

    /**
     * @param list<array{0: string, 1: string}> $rows
     */
    private function pdfWrap(string $title, array $f, array $rows): string
    {
        $h = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            body{font-family:DejaVu Sans;font-size:11px;color:#111;}
            h1{font-size:16px;} table{border-collapse:collapse;width:100%;}
            td,th{border:1px solid #ccc;padding:4px 6px;text-align:left;}
            th{background:#eee;}
            .meta{color:#444;font-size:10px;margin-bottom:12px;}
            </style></head><body>';
        $h .= '<h1>' . $this->pdfEscape($title) . '</h1>';
        $h .= '<div class="meta">Range: ' . $this->pdfEscape($f['date_from'] . ' — ' . $f['date_to']) . '</div>';
        $h .= '<table><thead><tr><th>Metric</th><th>Value</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $h .= '<tr><td>' . $this->pdfEscape($r[0]) . '</td><td>' . $this->pdfEscape($r[1]) . '</td></tr>';
        }
        $h .= '</tbody></table></body></html>';

        return $h;
    }

    private function pdfWorksHtml(array $f): string
    {
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;
        $wm = model(WorkModel::class);
        $rows = [];
        foreach ($wm->countsByWorkTypeWindow($f['range_start'], $f['range_end'], $wt) as $r) {
            $rows[] = [(string) ($r['work_type'] ?? ''), (string) ($r['c'] ?? 0)];
        }

        return $this->pdfTable('Works report — by type', $f, $rows, ['Type', 'Count']);
    }

    private function pdfLicensesHtml(array $f): string
    {
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;
        $ls = $f['license_status'] !== '' ? $f['license_status'] : null;
        $lm   = model(LicenseModel::class);
        $snap = $lm->reportStatusSnapshot($wt, $ls);
        $rev  = $lm->revenueSnapshot($wt, $ls);
        $rows = [
            ['Active', (string) $snap['active']],
            ['Expired', (string) $snap['expired']],
            ['Expiring ≤30d', (string) $snap['expiring_30']],
            ['Paid sum', (string) $rev['paid_sum']],
            ['Unpaid sum', (string) $rev['unpaid_sum']],
        ];

        return $this->pdfTable('License report', $f, $rows, ['Metric', 'Value']);
    }

    private function pdfUsageHtml(array $f): string
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            return $this->pdfWrap('Usage report', $f, [['Status', 'Schema not available']]);
        }
        $wt = $f['work_type'] !== '' ? $f['work_type'] : null;
        $um = model(UsageReportModel::class);
        $rows = [];
        foreach ($um->countsByUsageTypeBetween($f['range_start'], $f['range_end'], $wt) as $k => $v) {
            $rows[] = [$k, (string) $v];
        }

        return $this->pdfTable('Usage report — by disposition', $f, $rows, ['Usage type', 'Count']);
    }

    private function pdfCasesHtml(array $f): string
    {
        if (! InfringementCaseModel::schemaReady()) {
            return $this->pdfWrap('Cases report', $f, [['Status', 'Schema not available']]);
        }
        $cm = model(InfringementCaseModel::class);
        $st = $f['case_status'] !== '' ? $f['case_status'] : null;
        $or = $cm->countOpenVsResolvedSnapshot($st);
        $rows = [
            ['Open', (string) $or['open']],
            ['Resolved', (string) $or['resolved']],
            ['Cases in range', (string) $cm->countFiltered($st, $f['range_start'], $f['range_end'])],
            ['Avg resolution days', (string) ($cm->averageResolutionDaysBetween($f['range_start'], $f['range_end']) ?? '—')],
        ];

        return $this->pdfTable('Infringement report', $f, $rows, ['Metric', 'Value']);
    }

    private function pdfActivityHtml(array $f): string
    {
        if (! AuditLogModel::schemaReady()) {
            return $this->pdfWrap('Activity report', $f, [['Status', 'Audit log not available']]);
        }
        $am = model(AuditLogModel::class);
        $rows = [];
        foreach ($am->listUsersByActionCountBetween($f['range_start'], $f['range_end'], 40) as $u) {
            $nm = (string) ($u['display_name'] ?? $u['email'] ?? '');
            $rows[] = [$nm, (string) ($u['action_count'] ?? 0)];
        }

        return $this->pdfTable('User activity', $f, $rows, ['User', 'Actions']);
    }

    /**
     * @param list<array{0: string, 1: string}> $rows
     * @param array{0: string, 1: string}       $headers
     */
    private function pdfTable(string $title, array $f, array $rows, array $headers): string
    {
        $h = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            body{font-family:DejaVu Sans;font-size:11px;color:#111;}
            h1{font-size:16px;} table{border-collapse:collapse;width:100%;}
            td,th{border:1px solid #ccc;padding:4px 6px;text-align:left;}
            th{background:#eee;}
            .meta{color:#444;font-size:10px;margin-bottom:12px;}
            </style></head><body>';
        $h .= '<h1>' . $this->pdfEscape($title) . '</h1>';
        $h .= '<div class="meta">Range: ' . $this->pdfEscape($f['date_from'] . ' — ' . $f['date_to']) . '</div>';
        $h .= '<table><thead><tr><th>' . $this->pdfEscape($headers[0]) . '</th><th>' . $this->pdfEscape($headers[1]) . '</th></tr></thead><tbody>';
        foreach ($rows as $r) {
            $h .= '<tr><td>' . $this->pdfEscape($r[0]) . '</td><td>' . $this->pdfEscape($r[1]) . '</td></tr>';
        }
        $h .= '</tbody></table></body></html>';

        return $h;
    }
}
