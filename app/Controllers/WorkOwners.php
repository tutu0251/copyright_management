<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\OwnerModel;
use App\Models\WorkModel;
use App\Models\WorkOwnerModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class WorkOwners extends BaseController
{
    protected $helpers = ['form', 'url', 'auth'];

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'   => 'Work owners',
            'currentPage' => 'assets',
            'currentUser' => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'           => [
                ['id' => 'dashboard', 'label' => 'Dashboard', 'path' => 'dashboard'],
                ['id' => 'assets', 'label' => 'Assets', 'path' => 'works'],
                ['id' => 'owners', 'label' => 'Owners', 'path' => 'owners'],
                ['id' => 'licensees', 'label' => 'Licensees', 'path' => 'licensees'],
                ['id' => 'licenses', 'label' => 'Licenses', 'path' => 'licenses'],
                ['id' => 'monitoring', 'label' => 'Monitoring', 'path' => 'mockup/monitoring'],
                ['id' => 'cases', 'label' => 'Cases', 'path' => 'mockup/cases'],
                ['id' => 'reports', 'label' => 'Reports', 'path' => 'mockup/reports'],
                ['id' => 'settings', 'label' => 'Settings', 'path' => 'mockup/settings'],
            ],
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => 'Copyright Management · Ownership',
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(string $workId): string
    {
        $wid = (int) $workId;
        if ($wid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workModel = model(WorkModel::class);
        $workRow   = $workModel->find($wid);
        if ($workRow === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workOwnerModel = model(WorkOwnerModel::class);
        $links          = $workOwnerModel->forWorkWithOwners($wid);
        $totalPct       = $workOwnerModel->sumActivePercentageForWork($wid);

        $linkedOwnerIds = array_map(static fn (array $r): int => (int) ($r['owner_id'] ?? 0), $links);
        $allOwners      = model(OwnerModel::class)->listForSelect();
        $pickOwners     = array_values(array_filter(
            $allOwners,
            static fn (array $o): bool => ! in_array((int) ($o['id'] ?? 0), $linkedOwnerIds, true),
        ));

        return $this->layout('works/owners', [
            'pageTitle'    => 'Ownership · ' . (string) $workRow['title'],
            'work'         => $workRow,
            'links'        => $links,
            'pickOwners'   => $pickOwners,
            'totalPercent' => $totalPct,
            'errors'       => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
            'message'      => session()->getFlashdata('message'),
        ]);
    }

    public function store(string $workId): ResponseInterface|string
    {
        $wid = (int) $workId;
        if ($wid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workModel = model(WorkModel::class);
        if ($workModel->find($wid) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $post = $this->request->getPost();
        $row  = $this->normalizeWorkOwnerPost($wid, $post);

        $workOwnerModel = model(WorkOwnerModel::class);
        if ($workOwnerModel->hasActiveLink($wid, (int) $row['owner_id'])) {
            return redirect()->back()->withInput()->with('errors', ['owner_id' => 'This owner is already linked to the work.']);
        }

        $add = (float) $row['ownership_percentage'];
        if ($row['status'] === WorkOwnerModel::STATUS_ACTIVE) {
            $current = $workOwnerModel->sumActivePercentageForWork($wid);
            if (round($current + $add, 2) > 100.0) {
                return redirect()->back()->withInput()->with(
                    'errors',
                    ['ownership_percentage' => 'Total active ownership for this work cannot exceed 100% (currently ' . $current . '% active).'],
                );
            }
        }

        if (! $workOwnerModel->validate($row)) {
            return redirect()->back()->withInput()->with('errors', $workOwnerModel->errors());
        }

        $id = (int) $workOwnerModel->insert($row, true);
        if ($id < 1) {
            return redirect()->back()->withInput()->with('errors', $workOwnerModel->errors() ?: ['db' => 'Unable to save link.']);
        }

        return redirect()->to(site_url('works/' . $wid . '/owners'))->with('message', 'Owner linked to work.');
    }

    public function unlink(string $workId, string $workOwnerId): ResponseInterface
    {
        $wid = (int) $workId;
        $piv = (int) $workOwnerId;
        if ($wid < 1 || $piv < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workOwnerModel = model(WorkOwnerModel::class);
        $row            = $workOwnerModel->find($piv);
        if ($row === null || (int) $row['work_id'] !== $wid) {
            throw PageNotFoundException::forPageNotFound();
        }

        $workOwnerModel->delete($piv);

        return redirect()
            ->to(site_url('works/' . $wid . '/owners'))
            ->with('message', 'Owner unlinked from work.');
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function normalizeWorkOwnerPost(int $workId, array $post): array
    {
        $role = strtolower(trim((string) ($post['ownership_role'] ?? 'copyright_owner')));
        if (! in_array($role, WorkOwnerModel::ROLES, true)) {
            $role = WorkOwnerModel::ROLE_COPYRIGHT_OWNER;
        }

        $status = strtolower(trim((string) ($post['status'] ?? 'active')));
        if (! in_array($status, WorkOwnerModel::STATUSES, true)) {
            $status = WorkOwnerModel::STATUS_ACTIVE;
        }

        $start = trim((string) ($post['start_date'] ?? ''));
        $end   = trim((string) ($post['end_date'] ?? ''));

        $pctRaw = $post['ownership_percentage'] ?? '0';
        $pct    = is_numeric($pctRaw) ? (float) $pctRaw : 0.0;
        $pct    = round(max(0.0, min(100.0, $pct)), 2);

        return [
            'work_id'                => $workId,
            'owner_id'               => (int) ($post['owner_id'] ?? 0),
            'ownership_percentage'   => $pct,
            'ownership_role'         => $role,
            'start_date'             => $start === '' ? null : $start,
            'end_date'               => $end === '' ? null : $end,
            'status'                 => $status,
        ];
    }
}
