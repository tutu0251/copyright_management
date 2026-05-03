<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\CaseEvidenceModel;
use App\Models\CaseStatusLogModel;
use App\Models\InfringementCaseModel;
use App\Models\InfringementCaseNoteModel;
use App\Models\UsageReportModel;
use App\Models\UserModel;
use App\Models\WorkModel;
use App\Services\AuditLogService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;

class Cases extends BaseController
{
    protected $helpers = ['form', 'url', 'auth', 'permission', 'nav', 'locale'];

    private const EVIDENCE_MAX_BYTES = 10_485_760; // 10 MiB

    /** @var list<string> */
    private const EVIDENCE_ALLOWED_EXT = [
        'pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt', 'csv', 'md', 'zip',
    ];

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'     => lang('App.nav_cases'),
            'currentPage'   => 'cases',
            'currentUser'   => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'           => copyright_nav_items(),
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => lang('App.crumb_infringement_cases'),
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        if (! InfringementCaseModel::schemaReady()) {
            return $this->layout('cases/index', [
                'pageTitle'         => lang('App.nav_cases'),
                'rows'              => [],
                'searchQuery'       => '',
                'statusFilter'      => '',
                'priorityFilter'    => '',
                'migrationRequired' => true,
            ]);
        }

        $q        = trim((string) $this->request->getGet('q'));
        $status   = strtolower(trim((string) $this->request->getGet('case_status')));
        $priority = strtolower(trim((string) $this->request->getGet('priority')));
        if (! in_array($status, InfringementCaseModel::ALL_STATUSES, true)) {
            $status = '';
        }
        if (! in_array($priority, InfringementCaseModel::PRIORITIES, true)) {
            $priority = '';
        }

        $rows = model(InfringementCaseModel::class)->listIndexRows(
            $q !== '' ? $q : null,
            $status !== '' ? $status : null,
            $priority !== '' ? $priority : null,
        );

        $viewRows = [];
        foreach ($rows as $r) {
            $viewRows[] = $this->formatRowForList($r);
        }

        return $this->layout('cases/index', [
            'pageTitle'         => lang('App.nav_cases'),
            'rows'              => $viewRows,
            'searchQuery'       => $q,
            'statusFilter'      => $status,
            'priorityFilter'    => $priority,
            'migrationRequired' => false,
        ]);
    }

    public function create(): string|ResponseInterface
    {
        if (! InfringementCaseModel::schemaReady()) {
            return $this->layout('cases/create', [
                'pageTitle'         => lang('App.cases_open_case'),
                'works'             => [],
                'users'             => [],
                'prefillWorkId'     => null,
                'prefillReportId'   => null,
                'reportSummary'     => null,
                'errors'            => session()->getFlashdata('errors') ?? [],
                'migrationRequired' => true,
            ]);
        }

        $workId   = (int) $this->request->getGet('work_id');
        $reportId = (int) $this->request->getGet('usage_report_id');
        if ($reportId < 1) {
            $reportId = (int) old('usage_report_id', '0');
        }

        $works = model(WorkModel::class)->select('id, title')->orderBy('title', 'ASC')->limit(500)->findAll();
        $users = $this->usersForAssign();

        $reportSummary = null;
        if ($reportId > 0 && UsageReportModel::monitoringSchemaReady()) {
            $rep = model(UsageReportModel::class)->findWithWork($reportId);
            if ($rep !== null) {
                if (! empty($rep['infringement_case_id'])) {
                    return redirect()->to(site_url('cases/' . (int) $rep['infringement_case_id']))
                        ->with('errors', ['A case is already linked to this usage report.']);
                }
                $workId = (int) ($rep['work_id'] ?? $workId);
                $reportSummary = [
                    'id'              => $reportId,
                    'work_title'      => (string) ($rep['work_title'] ?? ''),
                    'detected_source' => (string) ($rep['detected_source'] ?? ''),
                    'usage_type'      => (string) ($rep['usage_type'] ?? ''),
                ];
            }
        }

        return $this->layout('cases/create', [
            'pageTitle'         => lang('App.cases_open_case'),
            'works'             => $works,
            'users'             => $users,
            'prefillWorkId'     => $workId > 0 ? $workId : null,
            'prefillReportId'   => $reportSummary !== null ? $reportId : null,
            'reportSummary'     => $reportSummary,
            'errors'            => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
            'migrationRequired' => false,
        ]);
    }

    public function store(): ResponseInterface|string
    {
        if (! InfringementCaseModel::schemaReady()) {
            return redirect()->to(site_url('cases'))->with('errors', ['Run `php spark migrate` to enable infringement case tables.']);
        }

        $post     = $this->request->getPost();
        $reportId = (int) ($post['usage_report_id'] ?? 0);
        $user     = auth_user();
        $rep      = null;

        if ($reportId > 0) {
            if (! UsageReportModel::monitoringSchemaReady()) {
                return redirect()->back()->withInput()->with('errors', ['usage_report_id' => 'Usage monitoring is not available.']);
            }
            $rep = model(UsageReportModel::class)->findWithWork($reportId);
            if ($rep === null) {
                return redirect()->back()->withInput()->with('errors', ['usage_report_id' => 'Usage report not found.']);
            }
            if (! empty($rep['infringement_case_id'])) {
                return redirect()->to(site_url('cases/' . (int) $rep['infringement_case_id']))
                    ->with('errors', ['A case already exists for this usage report.']);
            }
            if (model(InfringementCaseModel::class)->usageReportAlreadyLinked($reportId)) {
                return redirect()->back()->withInput()->with('errors', ['usage_report_id' => 'A case already references this usage report.']);
            }
        }

        $payload = $this->normalizeCasePayload($post, true);
        if ($reportId > 0 && $rep !== null) {
            $payload['work_id'] = (int) $rep['work_id'];
        }

        $caseModel = model(InfringementCaseModel::class);
        if (! $caseModel->validate($payload)) {
            return redirect()->back()->withInput()->with('errors', $caseModel->errors());
        }

        if (! $this->assertWorkExists((int) $payload['work_id'])) {
            return redirect()->back()->withInput()->with('errors', ['work_id' => 'Selected work was not found.']);
        }

        if ($reportId > 0 && $rep !== null && (int) $rep['work_id'] !== (int) $payload['work_id']) {
            return redirect()->back()->withInput()->with('errors', ['work_id' => 'Work must match the usage report’s work.']);
        }

        $dbConn = db_connect();
        $dbConn->transStart();

        $caseId = (int) $caseModel->insert($payload, true);
        if ($caseId < 1) {
            $dbConn->transRollback();

            return redirect()->back()->withInput()->with('errors', $caseModel->errors() ?: ['db' => 'Unable to create case.']);
        }

        $this->logStatusChange($caseId, null, (string) $payload['case_status'], null, $user['id'] ?? null);

        if ($reportId > 0) {
            model(UsageReportModel::class)->update($reportId, ['infringement_case_id' => $caseId]);
        }

        $evErr = $this->processEvidenceUpload($caseId, $user['id'] ?? null);
        if ($evErr !== null) {
            $dbConn->transRollback();

            return redirect()->back()->withInput()->with('errors', [$evErr]);
        }

        $dbConn->transComplete();

        service('auditLog')->log(
            AuditLogService::ACTION_CREATE,
            AuditLogService::ENTITY_CASE,
            $caseId,
            null,
            array_merge($payload, ['id' => $caseId]),
        );

        return redirect()->to(site_url('cases/' . $caseId))->with('message', 'Case created.');
    }

    public function show(string $id): ResponseInterface|string
    {
        if (! InfringementCaseModel::schemaReady()) {
            return redirect()->to(site_url('cases'))->with('errors', ['Run database migrations for case management.']);
        }

        $cid = (int) $id;
        if ($cid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $caseModel = model(InfringementCaseModel::class);
        $row       = $caseModel->findWithRelations($cid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $usageReport = null;
        $urId        = (int) ($row['usage_report_id'] ?? 0);
        if ($urId > 0 && UsageReportModel::monitoringSchemaReady()) {
            $usageReport = model(UsageReportModel::class)->findWithWork($urId);
        }

        $evidenceRows = model(CaseEvidenceModel::class)->forCase($cid);
        $evidence     = [];
        foreach ($evidenceRows as $er) {
            $evidence[] = $this->formatEvidenceRow($er, $cid);
        }

        $timeline = $this->buildTimeline($cid);

        $auditHistory    = [];
        $auditHistoryUrl = null;
        if (AuditLogModel::schemaReady()) {
            $auditHistory    = model(AuditLogModel::class)->listForEntity(AuditLogService::ENTITY_CASE, $cid, 25);
            $auditHistoryUrl = site_url('activities?entity_type=case&entity_id=' . $cid);
        }

        return $this->layout('cases/show', [
            'pageTitle'           => lang('App.cases_page_view', ['title' => (string) ($row['case_title'] ?? '')]),
            'caseRow'             => $this->formatCaseForView($row),
            'usageReport'         => $usageReport,
            'evidence'            => $evidence,
            'timeline'            => $timeline,
            'users'               => $this->usersForAssign(),
            'statuses'            => InfringementCaseModel::ALL_STATUSES,
            'errors'              => session()->getFlashdata('errors') ?? [],
            'auditHistory'        => $auditHistory,
            'auditHistoryMoreUrl' => $auditHistoryUrl,
        ]);
    }

    public function edit(string $id): ResponseInterface|string
    {
        if (! InfringementCaseModel::schemaReady()) {
            return redirect()->to(site_url('cases'));
        }

        $cid = (int) $id;
        if ($cid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $caseModel = model(InfringementCaseModel::class);
        $row       = $caseModel->findWithRelations($cid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->layout('cases/edit', [
            'pageTitle' => lang('App.cases_page_edit'),
            'caseRow'   => $row,
            'works'     => model(WorkModel::class)->select('id, title')->orderBy('title', 'ASC')->limit(500)->findAll(),
            'users'     => $this->usersForAssign(),
            'errors'    => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function update(string $id): ResponseInterface|string
    {
        if (! InfringementCaseModel::schemaReady()) {
            return redirect()->to(site_url('cases'));
        }

        $cid = (int) $id;
        if ($cid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $caseModel = model(InfringementCaseModel::class);
        $existing  = $caseModel->find($cid);
        if ($existing === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $post = $this->request->getPost();
        $payload = $this->normalizeCasePayload($post, false);
        $payload['usage_report_id'] = $existing['usage_report_id'] ?? null;
        $payload['case_status']       = (string) ($existing['case_status'] ?? InfringementCaseModel::STATUS_DETECTED);

        $merged = array_merge($existing, $payload);
        if (! $caseModel->validate($merged)) {
            return redirect()->back()->withInput()->with('errors', $caseModel->errors());
        }

        if (! $this->assertWorkExists((int) $payload['work_id'])) {
            return redirect()->back()->withInput()->with('errors', ['work_id' => 'Selected work was not found.']);
        }

        service('auditLog')->log(
            AuditLogService::ACTION_UPDATE,
            AuditLogService::ENTITY_CASE,
            $cid,
            $existing,
            $payload,
        );

        $caseModel->update($cid, $payload);

        return redirect()->to(site_url('cases/' . $cid))->with('message', 'Case updated.');
    }

    public function delete(string $id): ResponseInterface
    {
        if (! InfringementCaseModel::schemaReady()) {
            return redirect()->to(site_url('cases'));
        }

        $cid = (int) $id;
        if ($cid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $caseModel = model(InfringementCaseModel::class);
        $existing  = $caseModel->find($cid);
        if ($existing === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        service('auditLog')->log(
            AuditLogService::ACTION_DELETE,
            AuditLogService::ENTITY_CASE,
            $cid,
            $existing,
            null,
        );

        $this->deleteCaseEvidenceFiles($cid);

        if (UsageReportModel::monitoringSchemaReady()) {
            $urRows = db_connect()->table('usage_reports')->where('infringement_case_id', $cid)->get()->getResultArray();
            foreach ($urRows as $r) {
                model(UsageReportModel::class)->update((int) $r['id'], ['infringement_case_id' => null]);
            }
        }

        $caseModel->delete($cid);

        return redirect()->to(site_url('cases'))->with('message', 'Case deleted.');
    }

    public function updateStatus(string $id): ResponseInterface
    {
        if (! InfringementCaseModel::schemaReady()) {
            return redirect()->to(site_url('cases'));
        }

        $cid = (int) $id;
        if ($cid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $caseModel = model(InfringementCaseModel::class);
        $existing  = $caseModel->find($cid);
        if ($existing === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $newStatus = strtolower(trim((string) $this->request->getPost('case_status')));
        if (! in_array($newStatus, InfringementCaseModel::ALL_STATUSES, true)) {
            return redirect()->back()->with('errors', ['Invalid status.']);
        }

        $prev = (string) ($existing['case_status'] ?? '');
        if ($prev === $newStatus) {
            return redirect()->back()->with('errors', ['Status is already set to that value.']);
        }

        $note = trim((string) $this->request->getPost('transition_note'));
        $user = auth_user();

        $caseModel->update($cid, ['case_status' => $newStatus]);
        $caseModel->syncClosedTimestamp($cid, $newStatus);

        $this->logStatusChange($cid, $prev !== '' ? $prev : null, $newStatus, $note !== '' ? $note : null, $user['id'] ?? null);

        $newAudit = ['case_status' => $newStatus];
        if ($note !== '') {
            $newAudit['transition_note'] = substr($note, 0, 500);
        }
        service('auditLog')->log(
            AuditLogService::ACTION_STATUS_CHANGE,
            AuditLogService::ENTITY_CASE,
            $cid,
            ['case_status' => $prev],
            $newAudit,
        );

        return redirect()->to(site_url('cases/' . $cid))->with('message', 'Status updated.');
    }

    public function addNote(string $id): ResponseInterface
    {
        if (! InfringementCaseModel::schemaReady() || ! InfringementCaseNoteModel::tableReady()) {
            return redirect()->to(site_url('cases'));
        }

        $cid = (int) $id;
        if ($cid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (model(InfringementCaseModel::class)->find($cid) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $body = trim((string) $this->request->getPost('note_body'));
        if ($body === '') {
            return redirect()->back()->with('errors', ['Note cannot be empty.']);
        }

        $user = auth_user();
        $noteModel = model(InfringementCaseNoteModel::class);
        $noteData  = [
            'infringement_case_id' => $cid,
            'user_id'              => $user['id'] ?? null,
            'body'                 => $body,
            'created_at'           => date('Y-m-d H:i:s'),
        ];
        if (! $noteModel->validate($noteData)) {
            return redirect()->back()->with('errors', $noteModel->errors());
        }
        $noteModel->insert($noteData);

        service('auditLog')->log(
            AuditLogService::ACTION_UPDATE,
            AuditLogService::ENTITY_CASE,
            $cid,
            null,
            [
                'note_added'   => true,
                'note_preview' => substr($body, 0, 400),
            ],
        );

        return redirect()->to(site_url('cases/' . $cid))->with('message', 'Note added.');
    }

    public function uploadEvidence(string $id): ResponseInterface
    {
        if (! InfringementCaseModel::schemaReady() || ! CaseEvidenceModel::tableReady()) {
            return redirect()->to(site_url('cases'));
        }

        $cid = (int) $id;
        if ($cid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (model(InfringementCaseModel::class)->find($cid) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $user = auth_user();
        $err  = $this->processEvidenceUpload($cid, $user['id'] ?? null);
        if ($err !== null) {
            return redirect()->back()->with('errors', [$err]);
        }

        service('auditLog')->log(
            AuditLogService::ACTION_UPDATE,
            AuditLogService::ENTITY_CASE,
            $cid,
            null,
            ['evidence_uploaded' => true],
        );

        return redirect()->to(site_url('cases/' . $cid))->with('message', 'Evidence uploaded.');
    }

    public function evidenceFile(string $caseId, string $fileId): ResponseInterface
    {
        if (! InfringementCaseModel::schemaReady() || ! CaseEvidenceModel::tableReady()) {
            throw PageNotFoundException::forPageNotFound();
        }

        $cid = (int) $caseId;
        $fid = (int) $fileId;
        if ($cid < 1 || $fid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (model(InfringementCaseModel::class)->find($cid) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $row = model(CaseEvidenceModel::class)->find($fid);
        if ($row === null || (int) ($row['infringement_case_id'] ?? 0) !== $cid) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rel = (string) ($row['stored_path'] ?? '');
        if ($rel === '' || ! str_starts_with($rel, 'uploads/cases/')) {
            throw PageNotFoundException::forPageNotFound();
        }

        $full = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
        if (! is_file($full)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $mime = (string) ($row['mime_type'] ?? 'application/octet-stream');
        if ($mime === '') {
            $mime = 'application/octet-stream';
        }

        return $this->response->setHeader('Content-Type', $mime)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Disposition', 'inline')
            ->setBody((string) file_get_contents($full));
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function normalizeCasePayload(array $post, bool $isCreate): array
    {
        $status = strtolower(trim((string) ($post['case_status'] ?? InfringementCaseModel::STATUS_DETECTED)));
        if (! in_array($status, InfringementCaseModel::ALL_STATUSES, true)) {
            $status = InfringementCaseModel::STATUS_DETECTED;
        }

        $priority = strtolower(trim((string) ($post['priority'] ?? InfringementCaseModel::PRIORITY_MEDIUM)));
        if (! in_array($priority, InfringementCaseModel::PRIORITIES, true)) {
            $priority = InfringementCaseModel::PRIORITY_MEDIUM;
        }

        $assigned = (int) ($post['assigned_to'] ?? 0);
        $reportId = (int) ($post['usage_report_id'] ?? 0);

        $openedAt = $this->normalizeDateTime((string) ($post['opened_at'] ?? ''));
        if ($openedAt === null && $isCreate) {
            $openedAt = date('Y-m-d H:i:s');
        }

        $payload = [
            'work_id'           => (int) ($post['work_id'] ?? 0),
            'usage_report_id'   => $reportId > 0 ? $reportId : null,
            'case_title'        => trim((string) ($post['case_title'] ?? '')),
            'case_status'       => $status,
            'priority'          => $priority,
            'assigned_to'       => $assigned > 0 ? $assigned : null,
            'description'       => trim((string) ($post['description'] ?? '')),
            'resolution_notes'  => trim((string) ($post['resolution_notes'] ?? '')),
        ];

        if ($openedAt !== null) {
            $payload['opened_at'] = $openedAt;
        }

        if ($isCreate) {
            $payload['closed_at'] = InfringementCaseModel::isTerminalStatus($status)
                ? date('Y-m-d H:i:s')
                : null;
        }

        return $payload;
    }

    private function normalizeDateTime(string $raw): ?string
    {
        $raw = trim(str_replace('T', ' ', $raw));
        if ($raw === '') {
            return null;
        }
        $ts = strtotime($raw);

        return $ts !== false ? date('Y-m-d H:i:s', $ts) : null;
    }

    private function assertWorkExists(int $workId): bool
    {
        if ($workId < 1) {
            return false;
        }

        return model(WorkModel::class)->find($workId) !== null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function usersForAssign(): array
    {
        return model(UserModel::class)
            ->select('id, display_name, email')
            ->where('is_active', 1)
            ->orderBy('display_name', 'ASC')
            ->limit(500)
            ->findAll();
    }

    private function logStatusChange(int $caseId, ?string $from, string $to, ?string $note, ?int $userId): void
    {
        if (! CaseStatusLogModel::tableReady()) {
            return;
        }

        model(CaseStatusLogModel::class)->insert([
            'infringement_case_id' => $caseId,
            'from_status'          => $from,
            'to_status'            => $to,
            'transition_note'      => $note,
            'changed_by'           => $userId,
            'created_at'           => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, string|int|null>
     */
    private function formatRowForList(array $r): array
    {
        $st = (string) ($r['case_status'] ?? '');

        return [
            'id'               => (int) ($r['id'] ?? 0),
            'case_title'       => (string) ($r['case_title'] ?? ''),
            'work_title'       => (string) ($r['work_title'] ?? ''),
            'work_id'          => (int) ($r['work_id'] ?? 0),
            'case_status'      => $st,
            'case_status_label' => InfringementCaseModel::statusLabel($st),
            'status_tone'      => InfringementCaseModel::statusBadgeTone($st),
            'priority'         => (string) ($r['priority'] ?? ''),
            'priority_label'   => InfringementCaseModel::priorityLabel((string) ($r['priority'] ?? '')),
            'priority_tone'    => InfringementCaseModel::priorityTone((string) ($r['priority'] ?? '')),
            'assignee_name'    => (string) ($r['assignee_name'] ?? ''),
            'opened_at'        => (string) ($r['opened_at'] ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatCaseForView(array $row): array
    {
        $st = (string) ($row['case_status'] ?? '');
        $pr = (string) ($row['priority'] ?? '');

        return [
            'id'                  => (int) ($row['id'] ?? 0),
            'case_title'          => (string) ($row['case_title'] ?? ''),
            'work_id'             => (int) ($row['work_id'] ?? 0),
            'work_title'          => (string) ($row['work_title'] ?? ''),
            'usage_report_id'     => isset($row['usage_report_id']) && $row['usage_report_id'] !== null && $row['usage_report_id'] !== ''
                ? (int) $row['usage_report_id'] : null,
            'case_status'         => $st,
            'case_status_label'   => InfringementCaseModel::statusLabel($st),
            'status_tone'         => InfringementCaseModel::statusBadgeTone($st),
            'priority'            => $pr,
            'priority_label'      => InfringementCaseModel::priorityLabel($pr),
            'priority_tone'       => InfringementCaseModel::priorityTone($pr),
            'assigned_to'         => isset($row['assigned_to']) && $row['assigned_to'] !== null && $row['assigned_to'] !== ''
                ? (int) $row['assigned_to'] : null,
            'assignee_name'       => (string) ($row['assignee_name'] ?? ''),
            'assignee_email'      => (string) ($row['assignee_email'] ?? ''),
            'opened_at'           => (string) ($row['opened_at'] ?? ''),
            'closed_at'           => (string) ($row['closed_at'] ?? ''),
            'description'         => (string) ($row['description'] ?? ''),
            'resolution_notes'    => (string) ($row['resolution_notes'] ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $er
     * @return array<string, mixed>
     */
    private function formatEvidenceRow(array $er, int $caseId): array
    {
        $mime = (string) ($er['mime_type'] ?? '');
        $fid  = (int) ($er['id'] ?? 0);

        return [
            'id'             => $fid,
            'original_name'  => (string) ($er['original_name'] ?? ''),
            'mime_type'      => $mime,
            'is_image'       => str_starts_with(strtolower($mime), 'image/'),
            'url'            => $fid > 0 ? site_url('cases/' . $caseId . '/files/' . $fid) : '',
            'created_at'     => (string) ($er['created_at'] ?? ''),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function buildTimeline(int $caseId): array
    {
        $items = [];

        if (CaseStatusLogModel::tableReady()) {
            foreach (model(CaseStatusLogModel::class)->forCase($caseId) as $log) {
                $from = $log['from_status'] ?? null;
                $to   = (string) ($log['to_status'] ?? '');
                $items[] = [
                    'type'       => 'status',
                    'at'         => (string) ($log['created_at'] ?? ''),
                    'label'      => 'Status',
                    'body'       => ($from !== null && $from !== ''
                        ? InfringementCaseModel::statusLabel((string) $from) . ' → '
                        : '') . InfringementCaseModel::statusLabel($to),
                    'sub'        => trim((string) ($log['transition_note'] ?? '')),
                    'actor'      => (string) ($log['actor_name'] ?? ''),
                ];
            }
        }

        if (InfringementCaseNoteModel::tableReady()) {
            foreach (model(InfringementCaseNoteModel::class)->forCase($caseId) as $n) {
                $items[] = [
                    'type'  => 'note',
                    'at'    => (string) ($n['created_at'] ?? ''),
                    'label' => 'Note',
                    'body'  => (string) ($n['body'] ?? ''),
                    'sub'   => '',
                    'actor' => (string) ($n['author_name'] ?? ''),
                ];
            }
        }

        usort($items, static function (array $a, array $b): int {
            return strcmp((string) ($a['at'] ?? ''), (string) ($b['at'] ?? ''));
        });

        return $items;
    }

    private function processEvidenceUpload(int $caseId, ?int $userId): ?string
    {
        if (! CaseEvidenceModel::tableReady()) {
            return 'Evidence storage is not migrated.';
        }

        $file = $this->request->getFile('evidence_file');
        if (! $file instanceof UploadedFile) {
            return null;
        }
        if (! $file->isValid()) {
            if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                return null;
            }

            return 'Evidence file failed to upload.';
        }

        $size = (int) $file->getSize();
        if ($size < 1) {
            return null;
        }
        if ($size > self::EVIDENCE_MAX_BYTES) {
            return 'Evidence exceeds the maximum size (' . (int) (self::EVIDENCE_MAX_BYTES / 1_048_576) . ' MiB).';
        }

        $ext = strtolower((string) $file->getClientExtension());
        if ($ext === '' || ! in_array($ext, self::EVIDENCE_ALLOWED_EXT, true)) {
            return 'Evidence file type is not allowed.';
        }

        $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'cases' . DIRECTORY_SEPARATOR;
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
            return 'Could not create evidence directory.';
        }

        $storedBase = bin2hex(random_bytes(16)) . '.' . $ext;
        $relative   = 'uploads/cases/' . $storedBase;
        $fullPath   = $targetDir . $storedBase;

        if (! $file->move($targetDir, $storedBase)) {
            return 'Could not store evidence file.';
        }

        $mime = (string) $file->getClientMimeType();
        if ($mime === '') {
            $mime = 'application/octet-stream';
        }

        model(CaseEvidenceModel::class)->insert([
            'infringement_case_id' => $caseId,
            'stored_path'          => $relative,
            'original_name'        => (string) $file->getClientName(),
            'mime_type'            => $mime,
            'uploaded_by'          => $userId,
            'created_at'           => date('Y-m-d H:i:s'),
        ]);

        return null;
    }

    private function deleteCaseEvidenceFiles(int $caseId): void
    {
        if (! CaseEvidenceModel::tableReady()) {
            return;
        }

        $rows = model(CaseEvidenceModel::class)->where('infringement_case_id', $caseId)->findAll();
        foreach ($rows as $row) {
            $rel = (string) ($row['stored_path'] ?? '');
            if ($rel === '' || ! str_starts_with($rel, 'uploads/cases/')) {
                continue;
            }
            $full = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
            if (is_file($full)) {
                @unlink($full);
            }
        }
    }
}
