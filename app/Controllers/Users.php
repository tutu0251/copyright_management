<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Services\AuditLogService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Validation;

class Users extends BaseController
{
    /** @var list<string> */
    private const ASSIGNABLE_ROLE_SLUGS = ['admin', 'manager', 'viewer'];

    protected $helpers = ['form', 'url', 'auth', 'permission', 'nav', 'locale'];

    private function layout(string $view, array $data = []): string
    {
        $user = auth_user();

        $defaults = [
            'pageTitle'   => lang('App.users_page_title'),
            'currentPage' => 'users',
            'currentUser' => [
                'name' => $user['display_name'] ?? 'User',
                'role' => auth_primary_role_label(),
            ],
            'nav'           => copyright_nav_items(),
            'useAuthLogout' => true,
            'useCharts'     => false,
            'chartPayload'  => null,
            'appCrumb'      => lang('App.crumb_users'),
        ];

        $payload            = array_merge($defaults, $data);
        $payload['content'] = view($view, $payload);

        return view('layouts/main', $payload);
    }

    public function index(): string
    {
        $users = model(UserModel::class)->listUsersWithRoles();

        return $this->layout('users/index', [
            'users' => $users,
        ]);
    }

    public function create(): string
    {
        return $this->layout('users/create', [
            'pageTitle'       => lang('App.users_create_title'),
            'assignableRoles' => model(RoleModel::class)->managementAssignableRoles(),
            'errors'          => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function store(): ResponseInterface
    {
        /** @var Validation $rulesConfig */
        $rulesConfig = config(Validation::class);
        $messages    = [
            'email' => [
                'is_unique' => lang('App.users_error_email_exists'),
            ],
            'password_confirm' => [
                'matches' => lang('App.users_error_password_mismatch'),
            ],
        ];
        if (! $this->validate($rulesConfig->userManagementCreate, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $postedRoles = $this->normalizePostedRoleSlugs($this->request->getPost('roles'));
        if ($postedRoles === []) {
            return redirect()->back()->withInput()->with('errors', ['roles' => lang('App.users_error_roles_required')]);
        }

        $roleIds = $this->resolveRoleIdsFromPost($postedRoles, 0);
        if ($roleIds === []) {
            return redirect()->back()->withInput()->with('errors', ['roles' => lang('App.users_error_roles_required')]);
        }

        $email       = strtolower(trim((string) $this->request->getPost('email')));
        $displayName = trim((string) $this->request->getPost('display_name'));
        $plain       = (string) $this->request->getPost('password');
        $isActive    = (string) $this->request->getPost('is_active') === '1' ? 1 : 0;

        $userModel = model(UserModel::class);
        $db        = $userModel->db;
        $db->transStart();

        if (! $userModel->insert([
            'email'         => $email,
            'password_hash' => $userModel->hashPassword($plain),
            'display_name'  => $displayName,
            'is_active'     => $isActive,
        ], false)) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        $newId = (int) $userModel->getInsertID();
        if ($newId < 1) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('errors', ['db' => lang('App.users_error_save_failed')]);
        }

        (new UserRoleModel())->replaceRolesForUser($newId, $roleIds);

        $db->transComplete();
        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('errors', ['db' => lang('App.users_error_save_failed')]);
        }

        if (AuditLogModel::schemaReady()) {
            service('auditLog')->log(
                AuditLogService::ACTION_USER_CREATED,
                AuditLogService::ENTITY_USER,
                $newId,
                null,
                [
                    'email'         => $email,
                    'display_name'  => $displayName,
                    'is_active'     => $isActive,
                    'role_slugs'    => $postedRoles,
                ],
                null,
                $this->request,
            );
        }

        return redirect()->to(site_url('users/' . $newId))->with('message', lang('App.users_msg_created'));
    }

    public function show(string $id): string
    {
        $uid = (int) $id;
        if ($uid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $userModel = model(UserModel::class);
        $row       = $userModel->find($uid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $packed = $userModel->packUserWithRoles($row);
        $user   = $userModel->stripSensitiveFields($packed['user']);

        return $this->layout('users/show', [
            'pageTitle' => (string) ($user['display_name'] ?? lang('App.users_page_title')),
            'user'      => $user,
            'roles'     => $packed['roles'],
        ]);
    }

    public function edit(string $id): string
    {
        $uid = (int) $id;
        if ($uid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $userModel = model(UserModel::class);
        $row       = $userModel->find($uid);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $packed = $userModel->packUserWithRoles($row);
        $user   = $userModel->stripSensitiveFields($packed['user']);

        $assignableSlugs = self::ASSIGNABLE_ROLE_SLUGS;
        $checkedSlugs    = [];
        foreach ($packed['roles'] as $r) {
            $slug = (string) ($r['slug'] ?? '');
            if (in_array($slug, $assignableSlugs, true)) {
                $checkedSlugs[] = $slug;
            }
        }

        return $this->layout('users/edit', [
            'pageTitle'       => lang('App.users_edit_title'),
            'user'            => $user,
            'assignableRoles' => model(RoleModel::class)->managementAssignableRoles(),
            'checkedSlugs'    => $checkedSlugs,
            'errors'          => session()->getFlashdata('errors') ?? [],
        ]);
    }

    public function update(string $id): ResponseInterface
    {
        $uid = (int) $id;
        if ($uid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $userModel = model(UserModel::class);
        $existing  = $userModel->find($uid);
        if ($existing === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $postedRoles = $this->normalizePostedRoleSlugs($this->request->getPost('roles'));
        if ($postedRoles === []) {
            return redirect()->back()->withInput()->with('errors', ['roles' => lang('App.users_error_roles_required')]);
        }

        $roleIds = $this->resolveRoleIdsFromPost($postedRoles, $uid);
        if ($roleIds === []) {
            return redirect()->back()->withInput()->with('errors', ['roles' => lang('App.users_error_roles_required')]);
        }

        $rules = [
            'display_name' => 'required|min_length[1]|max_length[120]',
            'email'        => "required|valid_email|is_unique[users.email,id,{$uid}]",
        ];
        $pw = trim((string) $this->request->getPost('password'));
        if ($pw !== '') {
            $rules['password']         = 'required|min_length[8]';
            $rules['password_confirm'] = 'required|matches[password]';
        }

        $messages = [
            'email' => [
                'is_unique' => lang('App.users_error_email_exists'),
            ],
            'password_confirm' => [
                'matches' => lang('App.users_error_password_mismatch'),
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email       = strtolower(trim((string) $this->request->getPost('email')));
        $displayName = trim((string) $this->request->getPost('display_name'));
        $isActive    = (string) $this->request->getPost('is_active') === '1' ? 1 : 0;

        $rolesChanged = $this->roleAssignmentChanged($uid, $postedRoles);

        $update = [
            'email'        => $email,
            'display_name' => $displayName,
            'is_active'    => $isActive,
        ];
        if ($pw !== '') {
            $update['password_hash'] = $userModel->hashPassword($pw);
        }

        $userModel->update($uid, $update);
        if ($userModel->errors() !== []) {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }

        $roleSlugsBeforeSave = $this->currentRoleSlugsSorted($uid);
        (new UserRoleModel())->replaceRolesForUser($uid, $roleIds);
        $roleSlugsAfterSave = $this->currentRoleSlugsSorted($uid);

        if (AuditLogModel::schemaReady()) {
            $audit = service('auditLog');
            $audit->log(
                AuditLogService::ACTION_USER_UPDATED,
                AuditLogService::ENTITY_USER,
                $uid,
                [
                    'email'        => (string) ($existing['email'] ?? ''),
                    'display_name' => (string) ($existing['display_name'] ?? ''),
                    'is_active'    => (int) ($existing['is_active'] ?? 0),
                ],
                [
                    'email'        => $email,
                    'display_name' => $displayName,
                    'is_active'    => $isActive,
                ],
                null,
                $this->request,
            );
            if ($rolesChanged) {
                $audit->log(
                    AuditLogService::ACTION_USER_ROLE_UPDATED,
                    AuditLogService::ENTITY_USER,
                    $uid,
                    ['role_slugs' => $roleSlugsBeforeSave],
                    ['role_slugs' => $roleSlugsAfterSave],
                    null,
                    $this->request,
                );
            }
        }

        return redirect()->to(site_url('users/' . $uid))->with('message', lang('App.users_msg_updated'));
    }

    public function deactivate(string $id): ResponseInterface
    {
        $uid = (int) $id;
        if ($uid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $me = auth_user();
        if ($me !== null && $me['id'] === $uid) {
            return redirect()->back()->with('error', lang('App.users_error_self_deactivate'));
        }

        $userModel = model(UserModel::class);
        $existing  = $userModel->find($uid);
        if ($existing === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ((int) ($existing['is_active'] ?? 0) !== 1) {
            return redirect()->to(site_url('users/' . $uid))->with('message', lang('App.users_msg_already_inactive'));
        }

        $userModel->update($uid, ['is_active' => 0]);

        if (AuditLogModel::schemaReady()) {
            service('auditLog')->log(
                AuditLogService::ACTION_USER_DEACTIVATED,
                AuditLogService::ENTITY_USER,
                $uid,
                ['is_active' => 1],
                ['is_active' => 0],
                null,
                $this->request,
            );
        }

        return redirect()->to(site_url('users/' . $uid))->with('message', lang('App.users_msg_deactivated'));
    }

    public function activate(string $id): ResponseInterface
    {
        $uid = (int) $id;
        if ($uid < 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $userModel = model(UserModel::class);
        $existing  = $userModel->find($uid);
        if ($existing === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        if ((int) ($existing['is_active'] ?? 0) === 1) {
            return redirect()->to(site_url('users/' . $uid))->with('message', lang('App.users_msg_already_active'));
        }

        $userModel->update($uid, ['is_active' => 1]);

        if (AuditLogModel::schemaReady()) {
            service('auditLog')->log(
                AuditLogService::ACTION_USER_ACTIVATED,
                AuditLogService::ENTITY_USER,
                $uid,
                ['is_active' => 0],
                ['is_active' => 1],
                null,
                $this->request,
            );
        }

        return redirect()->to(site_url('users/' . $uid))->with('message', lang('App.users_msg_activated'));
    }

    /**
     * @param mixed $raw
     *
     * @return list<string>
     */
    private function normalizePostedRoleSlugs($raw): array
    {
        if (! is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $v) {
            $s = (string) $v;
            if ($s !== '' && in_array($s, self::ASSIGNABLE_ROLE_SLUGS, true)) {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param list<string> $postedAssignableSlugs
     *
     * @return list<int>
     */
    private function resolveRoleIdsFromPost(array $postedAssignableSlugs, int $existingUserId): array
    {
        $roleModel = model(RoleModel::class);
        $selected  = [];
        foreach ($postedAssignableSlugs as $slug) {
            $row = $roleModel->findBySlug($slug);
            if ($row !== null) {
                $selected[] = (int) $row['id'];
            }
        }
        $selected = array_values(array_unique(array_filter($selected, static fn (int $id): bool => $id > 0)));

        if ($existingUserId < 1) {
            return $selected;
        }

        $kept = [];
        foreach ($roleModel->rolesForUser($existingUserId) as $r) {
            $slug = (string) ($r['slug'] ?? '');
            if ($slug !== '' && ! in_array($slug, self::ASSIGNABLE_ROLE_SLUGS, true)) {
                $rid = (int) ($r['id'] ?? 0);
                if ($rid > 0) {
                    $kept[] = $rid;
                }
            }
        }
        $kept = array_values(array_unique(array_filter($kept, static fn (int $id): bool => $id > 0)));

        return array_values(array_unique(array_merge($kept, $selected)));
    }

    /**
     * @param list<string> $postedAssignableSlugs
     */
    private function roleAssignmentChanged(int $userId, array $postedAssignableSlugs): bool
    {
        $roleModel = model(RoleModel::class);
        $oldFull   = [];
        foreach ($roleModel->rolesForUser($userId) as $r) {
            $s = (string) ($r['slug'] ?? '');
            if ($s !== '') {
                $oldFull[] = $s;
            }
        }
        sort($oldFull);

        $kept = [];
        foreach ($oldFull as $s) {
            if (! in_array($s, self::ASSIGNABLE_ROLE_SLUGS, true)) {
                $kept[] = $s;
            }
        }
        $newFull = array_merge($kept, $postedAssignableSlugs);
        $newFull = array_values(array_unique($newFull));
        sort($newFull);

        return $oldFull !== $newFull;
    }

    /**
     * @return list<string>
     */
    private function currentRoleSlugsSorted(int $userId): array
    {
        $slugs = [];
        foreach (model(RoleModel::class)->rolesForUser($userId) as $r) {
            $s = (string) ($r['slug'] ?? '');
            if ($s !== '') {
                $slugs[] = $s;
            }
        }
        sort($slugs);

        return $slugs;
    }
}
