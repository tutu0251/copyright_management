<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LicenseModel;
use App\Models\LicenseeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Licensees extends BaseController
{
    protected $helpers = ['form', 'url', 'auth', 'permission', 'nav'];

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'   => 'Licensees',
            'currentPage' => 'licensees',
            'currentUser' => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'           => copyright_nav_items(),
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => 'Copyright Management · Licensees',
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        $q     = trim((string) $this->request->getGet('q'));
        $model = model(LicenseeModel::class);
        if ($q !== '') {
            $model->groupStart()
                ->like('name', $q)
                ->orLike('email', $q)
                ->orLike('country', $q)
                ->groupEnd();
        }
        $rows = $model->orderBy('name', 'ASC')->limit(200)->findAll();

        return $this->layout('licensees/index', [
            'pageTitle'   => 'Licensees',
            'licensees'   => $rows,
            'searchQuery' => $q,
        ]);
    }

    public function create(): string
    {
        return $this->layout('licensees/create', [
            'pageTitle' => 'Create licensee',
            'errors'    => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function store(): ResponseInterface|string
    {
        $model = model(LicenseeModel::class);
        $post  = $this->normalizeLicenseePost($this->request->getPost());

        if (! $model->validate($post)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $id = (int) $model->insert($post, true);
        if ($id < 1) {
            return redirect()->back()->withInput()->with('errors', $model->errors() ?: ['db' => 'Unable to save licensee.']);
        }

        return redirect()->to(site_url('licensees/' . $id))->with('message', 'Licensee created.');
    }

    public function show(string $id): string
    {
        $lid = (int) $id;
        if ($lid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(LicenseeModel::class);
        $row   = $model->find($lid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $licenses = model(LicenseModel::class)->forLicensee($lid);

        return $this->layout('licensees/show', [
            'pageTitle' => $row['name'],
            'licensee'  => $row,
            'licenses'  => $licenses,
            'message'   => session()->getFlashdata('message'),
        ]);
    }

    public function edit(string $id): string
    {
        $lid = (int) $id;
        if ($lid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(LicenseeModel::class);
        $row   = $model->find($lid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->layout('licensees/edit', [
            'pageTitle' => 'Edit licensee',
            'licensee'  => $row,
            'errors'    => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function update(string $id): ResponseInterface|string
    {
        $lid = (int) $id;
        if ($lid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(LicenseeModel::class);
        if ($model->find($lid) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $post = $this->normalizeLicenseePost($this->request->getPost());
        if (! $model->validate($post)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $model->update($lid, $post);

        return redirect()->to(site_url('licensees/' . $lid))->with('message', 'Licensee updated.');
    }

    public function delete(string $id): ResponseInterface
    {
        $lid = (int) $id;
        if ($lid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(LicenseeModel::class);
        if ($model->find($lid) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $db = db_connect();
        $db->transStart();
        model(LicenseModel::class)->where('licensee_id', $lid)->delete();
        $model->delete($lid);
        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->with('errors', ['db' => 'Could not archive licensee.']);
        }

        return redirect()->to(site_url('licensees'))->with('message', 'Licensee archived and related licenses archived.');
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function normalizeLicenseePost(array $post): array
    {
        $type = strtolower(trim((string) ($post['licensee_type'] ?? 'individual')));
        if (! in_array($type, LicenseeModel::LICENSEE_TYPES, true)) {
            $type = LicenseeModel::TYPE_INDIVIDUAL;
        }

        return [
            'name'           => trim((string) ($post['name'] ?? '')),
            'licensee_type'  => $type,
            'email'          => trim((string) ($post['email'] ?? '')),
            'phone'          => trim((string) ($post['phone'] ?? '')),
            'address'        => trim((string) ($post['address'] ?? '')),
            'country'        => trim((string) ($post['country'] ?? '')),
            'notes'          => trim((string) ($post['notes'] ?? '')),
        ];
    }
}
