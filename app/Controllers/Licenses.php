<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LicenseeModel;
use App\Models\LicenseModel;
use App\Models\WorkModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Licenses extends BaseController
{
    protected $helpers = ['form', 'url', 'auth'];

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
            ['id' => 'monitoring', 'label' => 'Monitoring', 'path' => 'mockup/monitoring'],
            ['id' => 'cases', 'label' => 'Cases', 'path' => 'mockup/cases'],
            ['id' => 'reports', 'label' => 'Reports', 'path' => 'mockup/reports'],
            ['id' => 'settings', 'label' => 'Settings', 'path' => 'mockup/settings'],
        ];
    }

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'   => 'Licenses',
            'currentPage' => 'licenses',
            'currentUser' => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'           => $this->navItems(),
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => 'Copyright Management · Licenses',
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        $q    = trim((string) $this->request->getGet('q'));
        $rows = model(LicenseModel::class)->listIndexRows($q !== '' ? $q : null);

        return $this->layout('licenses/index', [
            'pageTitle'   => 'Licenses',
            'licenses'    => $rows,
            'searchQuery' => $q,
        ]);
    }

    public function create(): string
    {
        $workId     = (int) $this->request->getGet('work_id');
        $licenseeId = (int) $this->request->getGet('licensee_id');

        $works     = model(WorkModel::class)->select('id, title')->orderBy('title', 'ASC')->limit(500)->findAll();
        $licensees = model(LicenseeModel::class)->listForSelect();

        return $this->layout('licenses/create', [
            'pageTitle'        => 'Create license',
            'works'            => $works,
            'licensees'        => $licensees,
            'prefillWorkId'    => $workId > 0 ? $workId : null,
            'prefillLicenseeId' => $licenseeId > 0 ? $licenseeId : null,
            'errors'           => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function store(): ResponseInterface|string
    {
        $model = model(LicenseModel::class);
        $post  = $this->normalizeLicensePost($this->request->getPost());

        $fkErr = $this->validateForeignKeys($post);
        if ($fkErr !== null) {
            return redirect()->back()->withInput()->with('errors', [$fkErr]);
        }

        $dateErr = $this->validateDateOrder($post);
        if ($dateErr !== null) {
            return redirect()->back()->withInput()->with('errors', [$dateErr]);
        }

        if (! $model->validate($post)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $id = (int) $model->insert($post, true);
        if ($id < 1) {
            return redirect()->back()->withInput()->with('errors', $model->errors() ?: ['db' => 'Unable to save license.']);
        }

        return redirect()->to(site_url('licenses/' . $id))->with('message', 'License created.');
    }

    public function show(string $id): string
    {
        $lid = (int) $id;
        if ($lid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $row = model(LicenseModel::class)->findWithRelations($lid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->layout('licenses/show', [
            'pageTitle' => 'License #' . $lid,
            'license'   => $row,
            'message'   => session()->getFlashdata('message'),
        ]);
    }

    public function edit(string $id): string
    {
        $lid = (int) $id;
        if ($lid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(LicenseModel::class);
        $row   = $model->find($lid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $works     = model(WorkModel::class)->select('id, title')->orderBy('title', 'ASC')->limit(500)->findAll();
        $licensees = model(LicenseeModel::class)->listForSelect();

        return $this->layout('licenses/edit', [
            'pageTitle' => 'Edit license',
            'license'   => $row,
            'works'     => $works,
            'licensees' => $licensees,
            'errors'    => session()->getFlashdata('errors') ?? service('validation')->getErrors(),
        ]);
    }

    public function update(string $id): ResponseInterface|string
    {
        $lid = (int) $id;
        if ($lid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(LicenseModel::class);
        if ($model->find($lid) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $post = $this->normalizeLicensePost($this->request->getPost());

        $fkErr = $this->validateForeignKeys($post);
        if ($fkErr !== null) {
            return redirect()->back()->withInput()->with('errors', [$fkErr]);
        }

        $dateErr = $this->validateDateOrder($post);
        if ($dateErr !== null) {
            return redirect()->back()->withInput()->with('errors', [$dateErr]);
        }

        if (! $model->validate($post)) {
            return redirect()->back()->withInput()->with('errors', $model->errors());
        }

        $model->update($lid, $post);

        return redirect()->to(site_url('licenses/' . $lid))->with('message', 'License updated.');
    }

    public function delete(string $id): ResponseInterface
    {
        $lid = (int) $id;
        if ($lid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model = model(LicenseModel::class);
        if ($model->find($lid) === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->delete($lid);

        return redirect()->to(site_url('licenses'))->with('message', 'License archived.');
    }

    /**
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    private function normalizeLicensePost(array $post): array
    {
        $type = strtolower(trim((string) ($post['license_type'] ?? 'non_exclusive')));
        if (! in_array($type, LicenseModel::LICENSE_TYPES, true)) {
            $type = LicenseModel::TYPE_NON_EXCLUSIVE;
        }

        $pay = strtolower(trim((string) ($post['payment_status'] ?? 'unpaid')));
        if (! in_array($pay, LicenseModel::PAYMENT_STATUSES, true)) {
            $pay = LicenseModel::PAYMENT_UNPAID;
        }

        $st = strtolower(trim((string) ($post['license_status'] ?? 'draft')));
        if (! in_array($st, LicenseModel::LICENSE_STATUSES, true)) {
            $st = LicenseModel::STATUS_DRAFT;
        }

        $start = trim((string) ($post['start_date'] ?? ''));
        $end   = trim((string) ($post['end_date'] ?? ''));
        if ($start === '') {
            $start = null;
        }
        if ($end === '') {
            $end = null;
        }

        $feeRaw = trim((string) ($post['fee_amount'] ?? ''));
        if ($feeRaw === '') {
            $fee = '0.00';
        } else {
            $fee = number_format((float) $feeRaw, 2, '.', '');
        }

        $cur = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) ($post['currency'] ?? 'USD')));
        if (strlen($cur) !== 3) {
            $cur = 'USD';
        }

        return [
            'work_id'         => (int) ($post['work_id'] ?? 0),
            'licensee_id'     => (int) ($post['licensee_id'] ?? 0),
            'license_type'    => $type,
            'territory'       => trim((string) ($post['territory'] ?? '')),
            'start_date'      => $start,
            'end_date'        => $end,
            'fee_amount'      => $fee,
            'currency'        => $cur,
            'payment_status'  => $pay,
            'license_status'  => $st,
            'terms'           => trim((string) ($post['terms'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $post
     */
    private function validateForeignKeys(array $post): ?string
    {
        $wid = (int) ($post['work_id'] ?? 0);
        $lid = (int) ($post['licensee_id'] ?? 0);
        if ($wid < 1 || model(WorkModel::class)->find($wid) === null) {
            return 'Select a valid work.';
        }
        if ($lid < 1 || model(LicenseeModel::class)->find($lid) === null) {
            return 'Select a valid licensee.';
        }

        return null;
    }

    /**
     * @param array<string, mixed> $post
     */
    private function validateDateOrder(array $post): ?string
    {
        $s = $post['start_date'] ?? null;
        $e = $post['end_date'] ?? null;
        if ($s === null || $s === '' || $e === null || $e === '') {
            return null;
        }
        $ts = strtotime((string) $s);
        $te = strtotime((string) $e);
        if ($ts !== false && $te !== false && $te < $ts) {
            return 'End date must be on or after the start date.';
        }

        return null;
    }
}
