<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\AuditLogModel;
use App\Models\UserModel;
use App\Services\AuditLogService;
use Config\Validation;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url', 'auth'];

    public function login()
    {
        return view('auth/login', [
            'pageTitle' => 'Sign in',
            'error'     => session()->getFlashdata('error'),
            'message'   => session()->getFlashdata('message'),
        ]);
    }

    public function attemptLogin()
    {
        /** @var Validation $rulesConfig */
        $rulesConfig = config(Validation::class);

        if (! $this->validate($rulesConfig->login)) {
            return redirect()->back()->withInput()->with('error', 'Enter a valid email and password.');
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        $userModel = new UserModel();
        $found     = $userModel->findActiveWithRolesByEmail($email);
        if ($found === null || ! $userModel->verifyPassword($password, $found['user']['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid credentials.');
        }

        $slugs = array_values(array_filter(array_map(static fn ($r) => (string) ($r['slug'] ?? ''), $found['roles'])));
        $names = array_values(array_filter(array_map(static fn ($r) => (string) ($r['name'] ?? ''), $found['roles'])));

        $session = session();
        $session->regenerate(true);
        $session->set([
            'auth_user_id'           => (int) $found['user']['id'],
            'auth_email'             => (string) $found['user']['email'],
            'auth_display_name'      => (string) $found['user']['display_name'],
            'auth_role_slugs'        => $slugs,
            'auth_primary_role_label'=> $names[0] ?? 'Member',
        ]);

        if (AuditLogModel::schemaReady()) {
            $uid = (int) $found['user']['id'];
            service('auditLog')->log(
                AuditLogService::ACTION_LOGIN,
                AuditLogService::ENTITY_USER,
                $uid,
                null,
                ['email' => (string) $found['user']['email']],
                $uid,
                $this->request,
            );
        }

        return redirect()->to(site_url('dashboard'));
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
            'auth_user_id',
            'auth_email',
            'auth_display_name',
            'auth_role_slugs',
            'auth_primary_role_label',
        ]);
        $session->regenerate(true);

        return redirect()->to(site_url('login'))->with('message', 'You have been signed out.');
    }
}
