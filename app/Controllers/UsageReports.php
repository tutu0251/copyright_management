<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UsageReportModel;
use App\Models\WorkModel;
use App\Services\AuditLogService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;

class UsageReports extends BaseController
{
    protected $helpers = ['form', 'url', 'auth'];

    private const EVIDENCE_MAX_BYTES = 10_485_760; // 10 MiB

    /** @var list<string> */
    private const EVIDENCE_ALLOWED_EXT = [
        'pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt', 'csv', 'md', 'zip',
    ];

    /**
     * @return list<array{id: string, label: string, path: string}>
     */
    private function navItems(): array
    {
        return [
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
        ];
    }

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'     => 'Usage reports',
            'currentPage'   => 'usage_reports',
            'currentUser'   => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'           => $this->navItems(),
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => 'Copyright Management · Usage monitoring',
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        $q          = trim((string) $this->request->getGet('q'));
        $usageType  = strtolower(trim((string) $this->request->getGet('usage_type')));
        if (! in_array($usageType, UsageReportModel::USAGE_TYPES, true)) {
            $usageType = '';
        }

        $schemaOk = UsageReportModel::monitoringSchemaReady();
        $rows     = $schemaOk
            ? model(UsageReportModel::class)->listIndexRows(
                $q !== '' ? $q : null,
                $usageType !== '' ? $usageType : null,
            )
            : [];

        $viewRows = [];
        foreach ($rows as $r) {
            $viewRows[] = $this->formatRowForList($r);
        }

        return $this->layout('usage_reports/index', [
            'pageTitle'          => 'Usage reports',
            'rows'               => $viewRows,
            'searchQuery'        => $q,
            'usageType'          => $usageType,
            'migrationRequired'  => ! $schemaOk,
        ]);
    }

    public function create(): string
    {
        $workId = (int) $this->request->getGet('work_id');
        $works  = model(WorkModel::class)->select('id, title')->orderBy('title', 'ASC')->limit(500)->findAll();

        return $this->layout('usage_reports/create', [
            'pageTitle'         => 'Report usage',
            'works'             => $works,
            'prefillWorkId'     => $workId > 0 ? $workId : null,
            'errors'            => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
            'migrationRequired' => ! UsageReportModel::monitoringSchemaReady(),
        ]);
    }

    public function store(): ResponseInterface|string
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            return redirect()->to(site_url('usage-reports'))->with('errors', [
                'Run database migrations: `php spark migrate` (Step 5 renames license usage snapshots and rebuilds `usage_reports`).',
            ]);
        }

        $model = model(UsageReportModel::class);
        $post  = $this->request->getPost();
        $user  = auth_user();

        $payload = $this->normalizePayload($post, $user['id'] ?? null, true);
        $vErr    = $this->validatePayloadExtras($payload);
        if ($vErr !== []) {
            return redirect()->back()->withInput()->with('errors', $vErr);
        }

        if (! $this->assertWorkExists((int) $payload['work_id'])) {
            return redirect()->back()->withInput()->with('errors', ['work_id' => 'Selected work was not found.']);
        }

        if (! $model->validate($payload)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $id = (int) $model->insert($payload, true);
        if ($id < 1) {
            return redirect()->back()->withInput()->with('errors', $model->errors() ?: ['db' => 'Unable to save usage report.']);
        }

        service('auditLog')->log(
            AuditLogService::ACTION_CREATE,
            AuditLogService::ENTITY_USAGE_REPORT,
            $id,
            null,
            array_merge($payload, ['id' => $id]),
        );

        $evErr = $this->processEvidenceUpload($id, $user['id'] ?? null);
        if ($evErr !== null) {
            return redirect()->back()->withInput()->with('errors', [$evErr]);
        }

        return redirect()->to(site_url('usage-reports/' . $id))->with('message', 'Usage report created.');
    }

    public function show(string $id): ResponseInterface|string
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            return redirect()->to(site_url('usage-reports'))->with('errors', [
                'Run `php spark migrate` to enable usage monitoring tables.',
            ]);
        }

        $rid = (int) $id;
        if ($rid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(UsageReportModel::class);
        $row   = $model->findWithWork($rid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $detail = $this->formatRowForShow($row);

        return $this->layout('usage_reports/show', [
            'pageTitle' => 'Usage #' . $rid,
            'report'    => $detail,
            'errors'    => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function edit(string $id): ResponseInterface|string
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            return redirect()->to(site_url('usage-reports'))->with('errors', [
                'Run `php spark migrate` to enable usage monitoring tables.',
            ]);
        }

        $rid = (int) $id;
        if ($rid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(UsageReportModel::class);
        $row   = $model->findWithWork($rid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $works = model(WorkModel::class)->select('id, title')->orderBy('title', 'ASC')->limit(500)->findAll();

        return $this->layout('usage_reports/edit', [
            'pageTitle' => 'Edit usage report',
            'report'    => $row,
            'works'     => $works,
            'errors'    => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function update(string $id): ResponseInterface|string
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            return redirect()->to(site_url('usage-reports'))->with('errors', [
                'Database is not migrated for usage monitoring. Run `php spark migrate`.',
            ]);
        }

        $rid = (int) $id;
        if ($rid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(UsageReportModel::class);
        $existing = $model->find($rid);
        if ($existing === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $user = auth_user();
        $post = $this->request->getPost();

        $payload = $this->normalizePayload($post, null, false);
        $rb      = $existing['reported_by'] ?? null;
        $payload['reported_by'] = $rb !== null && $rb !== '' ? (int) $rb : null;
        $vErr    = $this->validatePayloadExtras($payload);
        if ($vErr !== []) {
            return redirect()->back()->withInput()->with('errors', $vErr);
        }

        if (! $this->assertWorkExists((int) $payload['work_id'])) {
            return redirect()->back()->withInput()->with('errors', ['work_id' => 'Selected work was not found.']);
        }

        $toValidate = array_merge($existing, $payload);
        if (! $model->validate($toValidate)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        service('auditLog')->log(
            AuditLogService::ACTION_UPDATE,
            AuditLogService::ENTITY_USAGE_REPORT,
            $rid,
            $existing,
            $payload,
        );

        $model->update($rid, $payload);

        $remove = $this->request->getPost('remove_evidence');
        if ($remove === '1') {
            $this->deleteEvidenceFiles($existing);
            $model->update($rid, [
                'evidence_path'          => null,
                'evidence_mime_type'     => null,
                'evidence_uploaded_by'   => null,
                'evidence_uploaded_at'   => null,
            ]);
        }

        $evErr = $this->processEvidenceUpload($rid, $user['id'] ?? null);
        if ($evErr !== null) {
            return redirect()->back()->withInput()->with('errors', [$evErr]);
        }

        return redirect()->to(site_url('usage-reports/' . $rid))->with('message', 'Usage report updated.');
    }

    public function delete(string $id): ResponseInterface
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            return redirect()->to(site_url('usage-reports'));
        }

        $rid = (int) $id;
        if ($rid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(UsageReportModel::class);
        $row   = $model->find($rid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        service('auditLog')->log(
            AuditLogService::ACTION_DELETE,
            AuditLogService::ENTITY_USAGE_REPORT,
            $rid,
            $row,
            null,
        );

        $this->deleteEvidenceFiles($row);
        $model->delete($rid);

        return redirect()->to(site_url('usage-reports'))->with('message', 'Usage report archived.');
    }

    public function evidence(string $id): ResponseInterface
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rid = (int) $id;
        if ($rid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(UsageReportModel::class);
        $row   = $model->find($rid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $rel = (string) ($row['evidence_path'] ?? '');
        if ($rel === '' || ! str_starts_with($rel, 'uploads/evidence/')) {
            throw PageNotFoundException::forPageNotFound();
        }

        $full = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
        if (! is_file($full)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $mime = (string) ($row['evidence_mime_type'] ?? 'application/octet-stream');
        if ($mime === '') {
            $mime = 'application/octet-stream';
        }

        return $this->response->setHeader('Content-Type', $mime)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Disposition', 'inline')
            ->setBody((string) file_get_contents($full));
    }

    public function markAuthorized(string $id): ResponseInterface
    {
        return $this->setUsageType($id, UsageReportModel::USAGE_AUTHORIZED, 'Marked as authorized.');
    }

    public function markInfringement(string $id): ResponseInterface
    {
        return $this->setUsageType($id, UsageReportModel::USAGE_INFRINGEMENT, 'Marked as infringement.');
    }

    public function escalateCase(string $id): ResponseInterface
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            return redirect()->to(site_url('usage-reports'));
        }

        $rid = (int) $id;
        if ($rid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(UsageReportModel::class);
        $row   = $model->find($rid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! empty($row['infringement_case_id'])) {
            return redirect()->to(site_url('cases/' . (int) $row['infringement_case_id']))
                ->with('message', 'This usage report is already linked to a case.');
        }

        return redirect()->to(site_url('cases/create?usage_report_id=' . $rid));
    }

    private function setUsageType(string $id, string $usageType, string $message): ResponseInterface
    {
        if (! UsageReportModel::monitoringSchemaReady()) {
            return redirect()->to(site_url('usage-reports'));
        }

        $rid = (int) $id;
        if ($rid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! in_array($usageType, UsageReportModel::USAGE_TYPES, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(UsageReportModel::class);
        $row   = $model->find($rid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $prevType = (string) ($row['usage_type'] ?? '');
        service('auditLog')->log(
            AuditLogService::ACTION_STATUS_CHANGE,
            AuditLogService::ENTITY_USAGE_REPORT,
            $rid,
            ['usage_type' => $prevType],
            ['usage_type' => $usageType],
        );

        $model->update($rid, ['usage_type' => $usageType]);

        return redirect()->to(site_url('usage-reports/' . $rid))->with('message', $message);
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function normalizePayload(array $post, ?int $reportedBy, bool $isCreate): array
    {
        $method = strtolower(trim((string) ($post['detection_method'] ?? UsageReportModel::METHOD_MANUAL)));
        if (! in_array($method, UsageReportModel::DETECTION_METHODS, true)) {
            $method = UsageReportModel::METHOD_MANUAL;
        }

        $detectedType = strtolower(trim((string) ($post['detected_type'] ?? '')));
        if (! in_array($detectedType, UsageReportModel::DETECTED_TYPES, true)) {
            $detectedType = UsageReportModel::DETECTED_WEBSITE;
        }

        $usageType = strtolower(trim((string) ($post['usage_type'] ?? '')));
        if (! in_array($usageType, UsageReportModel::USAGE_TYPES, true)) {
            $usageType = UsageReportModel::USAGE_SUSPECTED;
        }

        $detectedAt = $this->normalizeDetectedAt((string) ($post['detected_at'] ?? ''));

        $payload = [
            'work_id'          => (int) ($post['work_id'] ?? 0),
            'detected_source'  => trim((string) ($post['detected_source'] ?? '')),
            'detected_type'    => $detectedType,
            'usage_type'       => $usageType,
            'detection_method' => $method,
            'detected_at'      => $detectedAt,
            'notes'            => trim((string) ($post['notes'] ?? '')),
        ];

        if ($isCreate) {
            $payload['reported_by'] = $reportedBy;
        }

        return $payload;
    }

    private function normalizeDetectedAt(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return date('Y-m-d H:i:s');
        }
        $raw = str_replace('T', ' ', $raw);
        $ts  = strtotime($raw);

        return $ts !== false ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<string>
     */
    private function validatePayloadExtras(array $payload): array
    {
        $err = [];
        if (! UsageReportModel::isValidDetectedSource((string) $payload['detected_source'])) {
            $err[] = 'Source must be a valid http(s) URL or a platform name.';
        }

        return $err;
    }

    private function assertWorkExists(int $workId): bool
    {
        if ($workId < 1) {
            return false;
        }

        return model(WorkModel::class)->find($workId) !== null;
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, string|int>
     */
    private function formatRowForList(array $r): array
    {
        $id = (int) ($r['id'] ?? 0);

        return [
            'id'              => $id,
            'work_title'      => (string) ($r['work_title'] ?? '—'),
            'work_id'         => (int) ($r['work_id'] ?? 0),
            'detected_source' => (string) ($r['detected_source'] ?? ''),
            'detected_type'   => UsageReportModel::detectedTypeLabel((string) ($r['detected_type'] ?? '')),
            'usage_type'      => (string) ($r['usage_type'] ?? ''),
            'usage_type_label' => UsageReportModel::usageTypeLabel((string) ($r['usage_type'] ?? '')),
            'usage_tone'      => UsageReportModel::usageTypeBadgeTone((string) ($r['usage_type'] ?? '')),
            'detected_at'     => (string) ($r['detected_at'] ?? ''),
            'detection_method' => UsageReportModel::detectionMethodLabel((string) ($r['detection_method'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $r
     * @return array<string, mixed>
     */
    private function formatRowForShow(array $r): array
    {
        $mime = (string) ($r['evidence_mime_type'] ?? '');
        $path = (string) ($r['evidence_path'] ?? '');

        return [
            'id'                   => (int) ($r['id'] ?? 0),
            'work_id'              => (int) ($r['work_id'] ?? 0),
            'work_title'           => (string) ($r['work_title'] ?? ''),
            'detected_source'      => (string) ($r['detected_source'] ?? ''),
            'is_url'               => preg_match('#^https?://#i', (string) ($r['detected_source'] ?? '')) === 1,
            'detected_type'        => (string) ($r['detected_type'] ?? ''),
            'detected_type_label'  => UsageReportModel::detectedTypeLabel((string) ($r['detected_type'] ?? '')),
            'usage_type'           => (string) ($r['usage_type'] ?? ''),
            'usage_type_label'     => UsageReportModel::usageTypeLabel((string) ($r['usage_type'] ?? '')),
            'usage_tone'           => UsageReportModel::usageTypeBadgeTone((string) ($r['usage_type'] ?? '')),
            'detection_method'     => (string) ($r['detection_method'] ?? ''),
            'detection_method_label' => UsageReportModel::detectionMethodLabel((string) ($r['detection_method'] ?? '')),
            'detected_at'          => (string) ($r['detected_at'] ?? ''),
            'notes'                => (string) ($r['notes'] ?? ''),
            'evidence_path'        => $path,
            'evidence_mime_type'   => $mime,
            'evidence_is_image'    => $path !== '' && str_starts_with(strtolower($mime), 'image/'),
            'evidence_url'         => $path !== '' ? site_url('usage-reports/' . (int) ($r['id'] ?? 0) . '/evidence') : '',
            'infringement_case_id' => isset($r['infringement_case_id']) && $r['infringement_case_id'] !== null && $r['infringement_case_id'] !== ''
                ? (int) $r['infringement_case_id'] : null,
        ];
    }

    private function processEvidenceUpload(int $reportId, ?int $userId): ?string
    {
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

        $targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'evidence' . DIRECTORY_SEPARATOR;
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
            return 'Could not create evidence directory.';
        }

        $storedBase = bin2hex(random_bytes(16)) . '.' . $ext;
        $relative   = 'uploads/evidence/' . $storedBase;
        $fullPath   = $targetDir . $storedBase;

        if (! $file->move($targetDir, $storedBase)) {
            return 'Could not store evidence file.';
        }

        $mime = (string) $file->getClientMimeType();
        if ($mime === '') {
            $mime = 'application/octet-stream';
        }

        $model = model(UsageReportModel::class);
        $prev  = $model->find($reportId);
        if ($prev !== null && ! empty($prev['evidence_path'])) {
            $this->deleteEvidenceFiles($prev);
        }

        $model->update($reportId, [
            'evidence_path'        => $relative,
            'evidence_mime_type'   => $mime,
            'evidence_uploaded_by' => $userId,
            'evidence_uploaded_at' => date('Y-m-d H:i:s'),
        ]);

        return null;
    }

    /**
     * @param array<string, mixed>|null $row
     */
    private function deleteEvidenceFiles(?array $row): void
    {
        if ($row === null) {
            return;
        }
        $rel = (string) ($row['evidence_path'] ?? '');
        if ($rel === '' || ! str_starts_with($rel, 'uploads/evidence/')) {
            return;
        }
        $full = WRITEPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rel);
        if (is_file($full)) {
            @unlink($full);
        }
    }
}
