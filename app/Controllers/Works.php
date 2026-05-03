<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LicenseModel;
use App\Models\UsageReportModel;
use App\Models\WorkFileModel;
use App\Models\WorkModel;
use App\Models\WorkOwnerModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;

class Works extends BaseController
{
    protected $helpers = ['form', 'url', 'auth'];

    /** Maximum total upload payload per request (bytes). */
    private const UPLOAD_MAX_BYTES = 10_485_760; // 10 MiB

    /** Allowed stored file extensions (lowercase). */
    private const UPLOAD_ALLOWED_EXT = [
        'pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'mp3', 'wav', 'mp4', 'webm',
        'txt', 'csv', 'md', 'doc', 'docx', 'xls', 'xlsx', 'zip',
    ];

    private const PER_PAGE = 12;

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'       => 'Assets',
            'currentPage'     => 'assets',
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
            'appCrumb'        => 'Copyright Management · Asset registry',
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        $page   = max(1, (int) $this->request->getGet('page'));
        $search = trim((string) $this->request->getGet('q'));

        $workModel = model(WorkModel::class);
        $bundle    = $workModel->getRegistryPage($page, self::PER_PAGE, $search);
        $rows      = $bundle['rows'];
        $total     = $bundle['total'];
        $page      = $bundle['page'];

        $totalPages = (int) max(1, (int) ceil($total / self::PER_PAGE));

        $works = [];
        foreach ($rows as $row) {
            $fmt                   = $workModel->formatForView($row);
            $fmt['license_count']  = (int) ($row['license_count'] ?? 0);
            $fmt['last_updated']     = $row['updated_at'] ?? null
                ? date('Y-m-d', strtotime((string) $row['updated_at']))
                : '—';
            $fmt['registration_date'] = $row['registered_at'] ?? null
                ? date('Y-m-d', strtotime((string) $row['registered_at']))
                : '—';
            $works[] = $fmt;
        }

        return $this->layout('works/index', [
            'pageTitle'   => 'Assets',
            'works'       => $works,
            'pager'       => [
                'page'       => $page,
                'perPage'    => self::PER_PAGE,
                'total'      => $total,
                'totalPages' => $totalPages,
            ],
            'searchQuery' => $search,
        ]);
    }

    public function create(): string
    {
        return $this->layout('works/create', [
            'pageTitle' => 'Register work',
            'errors'    => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function store(): ResponseInterface|string
    {
        $workModel = model(WorkModel::class);
        $post      = $this->request->getPost();

        $payload = $this->normalizeWorkPayload($post);
        $toValidate = $payload;
        if ($toValidate['registered_at'] === null) {
            unset($toValidate['registered_at']);
        }
        if (! $workModel->validate($toValidate)) {
            return redirect()->back()->withInput()->with('errors', $workModel->errors());
        }

        $user = auth_user();
        $payload['slug']       = $workModel->makeUniqueSlug((string) $payload['title']);
        $payload['created_by'] = $user['id'] ?? null;

        $workId = (int) $workModel->insert($payload, true);
        if ($workId < 1) {
            return redirect()->back()->withInput()->with('errors', $workModel->errors() ?: ['db' => 'Unable to save work.']);
        }

        $fileErrors = $this->processUploadedFiles($workId, $user['id'] ?? null);
        if ($fileErrors !== []) {
            // Work is saved; surface file issues without rolling back catalog row.
            session()->setFlashdata('warning', implode(' ', $fileErrors));
        }

        return redirect()->to(site_url('works/' . $workId))->with('message', 'Work created.');
    }

    public function show(string $id): string
    {
        $workId = (int) $id;
        if ($workId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workModel = model(WorkModel::class);
        $row       = $workModel->findWithLicenseCount($workId);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $work = $workModel->formatForView($row);
        $work['license_count'] = (int) ($row['license_count'] ?? 0);

        $fileModel = model(WorkFileModel::class);
        $files     = $fileModel->forWork($workId);

        $related = $this->loadRelatedRegistryData($workId);

        $monitoringDb = model(UsageReportModel::class)->forWork($workId);
        $usageMonitoringRows = [];
        foreach ($monitoringDb as $ur) {
            $urId = (int) ($ur['id'] ?? 0);
            $slug = (string) ($ur['usage_type'] ?? '');
            $usageMonitoringRows[] = [
                'id'               => (string) $urId,
                'source'           => (string) ($ur['detected_source'] ?? ''),
                'usage_type_label' => UsageReportModel::usageTypeLabel($slug),
                'usage_tone'       => UsageReportModel::usageTypeBadgeTone($slug),
                'detected_at'      => (string) ($ur['detected_at'] ?? ''),
            ];
        }

        return $this->layout('works/show', [
            'pageTitle'             => $work['title'],
            'work'                  => $work,
            'files'                 => $files,
            'workLicenses'          => $related['workLicenses'],
            'licenseUsageSnapshots' => $related['licenseUsageSnapshots'],
            'usageMonitoringRows'   => $usageMonitoringRows,
            'ownershipRows'         => $related['ownershipRows'],
            'flashMessage'  => session()->getFlashdata('message'),
            'flashWarning'  => session()->getFlashdata('warning'),
        ]);
    }

    public function edit(string $id): string
    {
        $workId = (int) $id;
        if ($workId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workModel = model(WorkModel::class);
        $row       = $workModel->find($workId);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $fileModel = model(WorkFileModel::class);
        $files     = $fileModel->forWork($workId);

        return $this->layout('works/edit', [
            'pageTitle' => 'Edit work',
            'work'      => $row,
            'files'     => $files,
            'errors'    => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function update(string $id): ResponseInterface|string
    {
        $workId = (int) $id;
        if ($workId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workModel = model(WorkModel::class);
        $existing  = $workModel->find($workId);
        if ($existing === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $post    = $this->request->getPost();
        $payload = $this->normalizeWorkPayload($post);
        $toValidate = $payload;
        if ($toValidate['registered_at'] === null) {
            unset($toValidate['registered_at']);
        }
        if (! $workModel->validate($toValidate)) {
            return redirect()->back()->withInput()->with('errors', $workModel->errors());
        }

        $workModel->update($workId, $payload);

        $user        = auth_user();
        $fileErrors  = $this->processUploadedFiles($workId, $user['id'] ?? null);
        if ($fileErrors !== []) {
            session()->setFlashdata('warning', implode(' ', $fileErrors));
        }

        return redirect()->to(site_url('works/' . $workId))->with('message', 'Work updated.');
    }

    public function delete(string $id): ResponseInterface
    {
        $workId = (int) $id;
        if ($workId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workModel = model(WorkModel::class);
        if ($workModel->find($workId) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workModel->delete($workId);

        return redirect()->to(site_url('works'))->with('message', 'Work archived.');
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function normalizeWorkPayload(array $post): array
    {
        $registered = isset($post['registered_at']) ? trim((string) $post['registered_at']) : '';
        if ($registered === '') {
            $registered = null;
        }

        $allowedStatus = ['draft', 'registered', 'pending_review', 'under_audit'];
        $status        = strtolower(trim((string) ($post['copyright_status'] ?? 'draft')));
        if (! in_array($status, $allowedStatus, true)) {
            $status = 'draft';
        }

        $risk = trim((string) ($post['risk_level'] ?? 'Low'));
        if (! in_array($risk, ['Low', 'Medium', 'High'], true)) {
            $risk = 'Low';
        }

        return [
            'title'             => trim((string) ($post['title'] ?? '')),
            'work_type'         => trim((string) ($post['work_type'] ?? '')),
            'creator'           => trim((string) ($post['creator'] ?? '')),
            'owner'             => trim((string) ($post['owner'] ?? '')),
            'copyright_status'  => $status,
            'risk_level'        => $risk,
            'description'       => trim((string) ($post['description'] ?? '')),
            'registered_at'     => $registered,
        ];
    }

    /**
     * Persist validated uploads under writable/uploads/works/.
     *
     * @return list<string> Human-readable errors (non-fatal for the whole transaction).
     */
    private function processUploadedFiles(int $workId, ?int $uploadedBy): array
    {
        $files = $this->request->getFileMultiple('evidence_files');
        if ($files === []) {
            return [];
        }

        $errors      = [];
        $totalBytes  = 0;
        $targetDir   = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'works' . DIRECTORY_SEPARATOR;
        if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
            return ['Could not create upload directory.'];
        }

        $fileModel = model(WorkFileModel::class);

        foreach ($files as $idx => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                if ($file instanceof UploadedFile && $file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $errors[] = 'One or more files failed to upload.';
                continue;
            }

            $size = (int) $file->getSize();
            if ($size < 1) {
                continue;
            }
            $totalBytes += $size;
            if ($totalBytes > self::UPLOAD_MAX_BYTES) {
                $errors[] = 'Total upload size exceeds the allowed limit (' . (int) (self::UPLOAD_MAX_BYTES / 1_048_576) . ' MiB).';

                break;
            }
            if ($size > self::UPLOAD_MAX_BYTES) {
                $errors[] = 'File "' . $file->getClientName() . '" exceeds the per-file size limit.';

                continue;
            }

            $ext = strtolower((string) $file->getClientExtension());
            if ($ext === '' || ! in_array($ext, self::UPLOAD_ALLOWED_EXT, true)) {
                $errors[] = 'File type .' . ($ext !== '' ? $ext : 'unknown') . ' is not allowed.';

                continue;
            }

            $original = basename((string) $file->getClientName());
            if ($original === '' || $original === '.' || $original === '..') {
                $errors[] = 'Invalid file name.';

                continue;
            }

            $storedBase = bin2hex(random_bytes(16)) . '.' . $ext;
            $relative   = 'uploads/works/' . $storedBase;
            $fullPath   = $targetDir . $storedBase;

            if (is_file($fullPath)) {
                $storedBase = bin2hex(random_bytes(20)) . '.' . $ext;
                $relative   = 'uploads/works/' . $storedBase;
                $fullPath   = $targetDir . $storedBase;
            }

            if (! $file->move($targetDir, $storedBase)) {
                $errors[] = 'Could not store file "' . $original . '".';

                continue;
            }

            $hash = hash_file('sha256', $fullPath);
            if ($hash !== false) {
                $hash = strtolower($hash);
            }
            if ($hash === false) {
                @unlink($fullPath);
                $errors[] = 'Could not hash file "' . $original . '".';

                continue;
            }

            $mime = (string) $file->getClientMimeType();
            if ($mime === '') {
                $mime = 'application/octet-stream';
            }

            $row = [
                'work_id'            => $workId,
                'original_filename'  => $original,
                'stored_filename'    => $storedBase,
                'storage_path'       => $relative,
                'mime_type'          => $mime,
                'size_bytes'         => $size,
                'sha256'             => $hash,
                'uploaded_by'        => $uploadedBy,
                'created_at'         => date('Y-m-d H:i:s'),
            ];

            if (! $fileModel->validate($row) || ! $fileModel->insert($row)) {
                @unlink($fullPath);
                $errors[] = 'Database rejected metadata for "' . $original . '".';
            }
        }

        return $errors;
    }

    /**
     * @return array{workLicenses: list<array<string, string>>, licenseUsageSnapshots: list<array<string, string>>, ownershipRows: list<array<string, string>>}
     */
    private function loadRelatedRegistryData(int $workId): array
    {
        $db = db_connect();

        $licRows = model(LicenseModel::class)->forWork($workId);

        $workLicenses = [];
        foreach ($licRows as $lr) {
            $licId = (int) ($lr['id'] ?? 0);
            $eff   = LicenseModel::effectiveStatus($lr);
            $fee   = (float) ($lr['fee_amount'] ?? 0);
            $cur   = (string) ($lr['currency'] ?? 'USD');
            $workLicenses[] = [
                'id'         => (string) $licId,
                'licensee'   => (string) ($lr['licensee_name'] ?? '—'),
                'type'       => LicenseModel::licenseTypeLabel((string) ($lr['license_type'] ?? '')),
                'territory'  => (string) ($lr['territory'] ?? '—'),
                'start_date' => $lr['start_date'] !== null && $lr['start_date'] !== '' ? (string) $lr['start_date'] : '—',
                'end_date'   => $lr['end_date'] !== null && $lr['end_date'] !== '' ? (string) $lr['end_date'] : '—',
                'fee'        => $cur . ' ' . number_format($fee, 2),
                'status'     => LicenseModel::statusLabel($eff),
                'eff'        => $eff,
            ];
        }

        $workOwnerModel = model(WorkOwnerModel::class);
        $ownRows         = $workOwnerModel->forWorkWithOwners($workId);
        $ownershipRows   = [];
        foreach ($ownRows as $o) {
            $start   = $o['start_date'] ?? null;
            $created = $o['created_at'] ?? null;
            $since   = $start ? (string) $start : ($created ? date('Y-m-d', strtotime((string) $created)) : '—');
            $ownershipRows[] = [
                'work_owner_id' => (string) ($o['id'] ?? ''),
                'owner_id'      => (string) ($o['owner_id'] ?? ''),
                'owner'         => (string) ($o['owner_name'] ?? ''),
                'share'         => number_format((float) ($o['ownership_percentage'] ?? 0), 2) . '%',
                'role'          => WorkOwnerModel::roleLabel((string) ($o['ownership_role'] ?? '')),
                'status'        => WorkOwnerModel::statusLabel((string) ($o['status'] ?? '')),
                'since'         => $since,
            ];
        }

        $licenseIds = array_column($licRows, 'id');
        $licenseUsageSnapshots = [];
        if ($licenseIds !== [] && $db->tableExists('license_usage_snapshots')) {
            $usageDbRows = $db->table('license_usage_snapshots')
                ->whereIn('license_id', $licenseIds)
                ->orderBy('period_start', 'DESC')
                ->get()
                ->getResultArray();
            foreach ($usageDbRows as $u) {
                $rev = $u['revenue_amount'] ?? null;
                $licenseUsageSnapshots[] = [
                    'period'       => ($u['period_start'] ?? '') . ' – ' . ($u['period_end'] ?? ''),
                    'channel'      => '—',
                    'impressions'  => $u['usage_units'] !== null ? (string) $u['usage_units'] : '—',
                    'revenue'      => $rev !== null ? '$' . number_format((float) $rev, 2) : '—',
                ];
            }
        }

        return [
            'workLicenses'          => $workLicenses,
            'licenseUsageSnapshots' => $licenseUsageSnapshots,
            'ownershipRows'         => $ownershipRows,
        ];
    }
}
