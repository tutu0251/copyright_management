<?php

declare(strict_types=1);

namespace App\Controllers\Main;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Auth extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        helper(['form', 'url', 'role']);
    }

    public function login()
    {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'email'    => 'required|valid_email',
                'password' => 'required',
            ];

            if (! $this->validateData($this->request->getPost(), $rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $email    = (string) $this->request->getPost('email');
            $password = (string) $this->request->getPost('password');

            $model = new UserModel();
            $user  = $model->findActiveByEmail($email);

            if ($user === null || ! $model->verifyPassword($password, $user['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
            }

            session()->regenerate(true);

            $roleSlugs = $model->getRoleSlugsForUser((int) $user['id']);

            session()->set([
                'auth_user_id'    => (int) $user['id'],
                'auth_email'      => $user['email'],
                'auth_name'       => $user['name'],
                'auth_role_slugs' => $roleSlugs,
            ]);

            return redirect()->to(site_url('main/dashboard'));
        }

        return view('main/login', [
            'pageTitle' => 'Sign in',
        ]);
    }

    public function logout(): ResponseInterface
    {
        session()->remove(['auth_user_id', 'auth_email', 'auth_name', 'auth_role_slugs']);
        session()->regenerate(true);

        return redirect()->to(site_url('main/login'))->with('auth_message', 'You have been signed out.');
    }
}
