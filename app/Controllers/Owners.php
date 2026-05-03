<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\OwnerModel;
use App\Models\WorkOwnerModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Owners extends BaseController
{
    protected $helpers = ['form', 'url', 'auth'];

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'   => 'Owners',
            'currentPage' => 'owners',
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
                ['id' => 'usage_reports', 'label' => 'Usage reports', 'path' => 'usage-reports'],
                ['id' => 'cases', 'label' => 'Cases', 'path' => 'cases'],
                ['id' => 'reports', 'label' => 'Reports', 'path' => 'mockup/reports'],
                ['id' => 'settings', 'label' => 'Settings', 'path' => 'mockup/settings'],
            ],
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => 'Copyright Management · Owners',
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        $q     = trim((string) $this->request->getGet('q'));
        $model = model(OwnerModel::class);
        if ($q !== '') {
            $model->groupStart()
                ->like('name', $q)
                ->orLike('email', $q)
                ->orLike('country', $q)
                ->groupEnd();
        }
        $owners = $model->orderBy('name', 'ASC')->limit(200)->findAll();

        return $this->layout('owners/index', [
            'pageTitle'   => 'Owners',
            'owners'      => $owners,
            'searchQuery' => $q,
        ]);
    }

    public function create(): string
    {
        return $this->layout('owners/create', [
            'pageTitle' => 'Create owner',
            'errors'    => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function store(): ResponseInterface|string
    {
        $model = model(OwnerModel::class);
        $post  = $this->normalizeOwnerPost($this->request->getPost());

        if (! $model->validate($post)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $id = (int) $model->insert($post, true);
        if ($id < 1) {
            return redirect()->back()->withInput()->with('errors', $model->errors() ?: ['db' => 'Unable to save owner.']);
        }

        return redirect()->to(site_url('owners/' . $id))->with('message', 'Owner created.');
    }

    public function show(string $id): string
    {
        $ownerId = (int) $id;
        if ($ownerId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(OwnerModel::class);
        $row   = $model->find($ownerId);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $links = model(WorkOwnerModel::class)->forOwnerWithWorks($ownerId);

        return $this->layout('owners/show', [
            'pageTitle' => $row['name'],
            'owner'     => $row,
            'workLinks' => $links,
            'message'   => session()->getFlashdata('message'),
        ]);
    }

    public function edit(string $id): string
    {
        $ownerId = (int) $id;
        if ($ownerId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(OwnerModel::class);
        $row   = $model->find($ownerId);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->layout('owners/edit', [
            'pageTitle' => 'Edit owner',
            'owner'     => $row,
            'errors'    => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function update(string $id): ResponseInterface|string
    {
        $ownerId = (int) $id;
        if ($ownerId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(OwnerModel::class);
        if ($model->find($ownerId) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $post = $this->normalizeOwnerPost($this->request->getPost());
        if (! $model->validate($post)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $model->update($ownerId, $post);

        return redirect()->to(site_url('owners/' . $ownerId))->with('message', 'Owner updated.');
    }

    public function delete(string $id): ResponseInterface
    {
        $ownerId = (int) $id;
        if ($ownerId < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(OwnerModel::class);
        if ($model->find($ownerId) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();
        $db->transStart();
        model(WorkOwnerModel::class)->where('owner_id', $ownerId)->delete();
        $model->delete($ownerId);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('errors', ['db' => 'Could not archive owner.']);
        }

        return redirect()->to(site_url('owners'))->with('message', 'Owner archived and unlinked from works.');
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function normalizeOwnerPost(array $post): array
    {
        $type = strtolower(trim((string) ($post['owner_type'] ?? 'individual')));
        if (! in_array($type, OwnerModel::OWNER_TYPES, true)) {
            $type = OwnerModel::TYPE_INDIVIDUAL;
        }

        return [
            'name'       => trim((string) ($post['name'] ?? '')),
            'owner_type' => $type,
            'email'      => trim((string) ($post['email'] ?? '')),
            'phone'      => trim((string) ($post['phone'] ?? '')),
            'address'    => trim((string) ($post['address'] ?? '')),
            'country'    => trim((string) ($post['country'] ?? '')),
            'notes'      => trim((string) ($post['notes'] ?? '')),
        ];
    }
}
