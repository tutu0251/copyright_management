<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Services\AuditLogService;
use Config\Validation;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url', 'auth', 'locale'];

    public function login()
    {
        return view('auth/login', [
            'pageTitle' => lang('App.auth_page_title'),
            'error'     => session()->getFlashdata('error'),
            'message'   => session()->getFlashdata('message'),
        ]);
    }

    public function attemptLogin()
    {
        /** @var Validation $rulesConfig */
        $rulesConfig = config(Validation::class);

        if (! $this->validate($rulesConfig->login)) {
            return redirect()->back()->withInput()->with('error', lang('App.auth_error_validation'));
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        $userModel = new UserModel();
        $row       = $userModel->findByEmail($email);

        if ($row === null) {
            $this->auditLoginFailed($email, null);

            return redirect()->back()->withInput()->with('error', lang('App.auth_error_invalid_credentials'));
        }

        if ((int) ($row['is_active'] ?? 0) !== 1) {
            $this->auditLoginFailed($email, (int) $row['id']);

            return redirect()->back()->withInput()->with('error', lang('App.auth_error_account_inactive'));
        }

        if (! $userModel->verifyPassword($password, (string) ($row['password_hash'] ?? ''))) {
            $this->auditLoginFailed($email, (int) $row['id']);

            return redirect()->back()->withInput()->with('error', lang('App.auth_error_invalid_credentials'));
        }

        $packed  = $userModel->packUserWithRoles($row);
        $slugs   = array_values(array_filter(array_map(static fn ($r) => (string) ($r['slug'] ?? ''), $packed['roles'])));
        $names   = array_values(array_filter(array_map(static fn ($r) => (string) ($r['name'] ?? ''), $packed['roles'])));
        $session = session();
        $session->regenerate(true);

        $uid   = (int) $packed['user']['id'];
        $mail  = (string) $packed['user']['email'];
        $label = (string) $packed['user']['display_name'];

        $session->set([
            'user_id'                 => $uid,
            'email'                   => $mail,
            'name'                    => $label,
            'role_slugs'              => $slugs,
            'is_logged_in'            => true,
            'auth_user_id'            => $uid,
            'auth_email'              => $mail,
            'auth_display_name'       => $label,
            'auth_role_slugs'         => $slugs,
            'auth_primary_role_label' => $names[0] ?? 'Member',
        ]);

        if (AuditLogModel::schemaReady()) {
            service('auditLog')->log(
                AuditLogService::ACTION_LOGIN,
                AuditLogService::ENTITY_USER,
                $uid,
                null,
                ['email' => $mail],
                $uid,
                $this->request,
            );
        }

        if ($userModel->db->fieldExists('last_login_at', 'users')) {
            $userModel->update($uid, ['last_login_at' => date('Y-m-d H:i:s')]);
        }

        return redirect()->to(site_url('dashboard'));
    }

    public function register()
    {
        return view('auth/register', [
            'pageTitle' => lang('App.auth_register_page_title'),
            'error'     => session()->getFlashdata('error'),
            'errors'    => session()->getFlashdata('errors'),
            'message'   => session()->getFlashdata('message'),
        ]);
    }

    public function attemptRegister()
    {
        /** @var Validation $rulesConfig */
        $rulesConfig = config(Validation::class);

        $messages = [
            'email' => [
                'is_unique' => lang('App.auth_error_email_exists'),
            ],
            'password_confirm' => [
                'matches' => lang('App.auth_error_password_mismatch'),
            ],
        ];

        if (! $this->validate($rulesConfig->register, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $roleModel = new RoleModel();
        $viewer    = $roleModel->findBySlug('viewer');
        if ($viewer === null) {
            return redirect()->back()->withInput()->with('error', lang('App.auth_error_viewer_role_missing'));
        }

        $name  = trim((string) $this->request->getPost('name'));
        $email = strtolower(trim((string) $this->request->getPost('email')));
        $plain = (string) $this->request->getPost('password');

        $userModel = new UserModel();
        $db        = $userModel->db;
        $db->transStart();

        if (! $userModel->insert([
            'email'          => $email,
            'password_hash'  => $userModel->hashPassword($plain),
            'display_name'   => $name,
            'is_active'      => 1,
        ], false)) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('error', lang('App.auth_error_registration_failed'));
        }

        $newId = (int) $userModel->getInsertID();
        if ($newId <= 0) {
            $db->transRollback();

            return redirect()->back()->withInput()->with('error', lang('App.auth_error_registration_failed'));
        }

        $db->table('user_roles')->insert([
            'user_id'     => $newId,
            'role_id'     => (int) $viewer['id'],
            'assigned_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', lang('App.auth_error_registration_failed'));
        }

        if (AuditLogModel::schemaReady()) {
            service('auditLog')->log(
                AuditLogService::ACTION_REGISTER,
                AuditLogService::ENTITY_USER,
                $newId,
                null,
                ['email' => $email, 'display_name' => $name],
                null,
                $this->request,
            );
        }

        return redirect()->to(site_url('login'))->with('message', lang('App.auth_registration_successful'));
    }

    public function logout()
    {
        $session = session();
        $user    = auth_user();
        if ($user !== null && AuditLogModel::schemaReady()) {
            $uid = (int) ($user['id'] ?? 0);
            if ($uid > 0) {
                service('auditLog')->log(
                    AuditLogService::ACTION_LOGOUT,
                    AuditLogService::ENTITY_USER,
                    $uid,
                    null,
                    ['email' => (string) ($user['email'] ?? '')],
                    $uid,
                    $this->request,
                );
            }
        }

        $session->remove([
            'user_id',
            'email',
            'name',
            'role_slugs',
            'is_logged_in',
            'auth_user_id',
            'auth_email',
            'auth_display_name',
            'auth_role_slugs',
            'auth_primary_role_label',
        ]);
        $session->regenerate(true);

        return redirect()->to(site_url('login'))->with('message', lang('App.auth_signed_out'));
    }

    private function auditLoginFailed(string $email, ?int $knownUserId): void
    {
        if (! AuditLogModel::schemaReady()) {
            return;
        }

        service('auditLog')->log(
            AuditLogService::ACTION_LOGIN_FAILED,
            AuditLogService::ENTITY_USER,
            $knownUserId,
            null,
            ['email' => $email],
            null,
            $this->request,
        );
    }
}
