<?php

declare(strict_types=1);

namespace App\Controllers\Main;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class Dashboard extends BaseController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        helper(['url', 'role']);
    }

    public function index(): string
    {
        $db = db_connect();

        $counts = [
            'works'               => $db->table('works')->countAllResults(),
            'work_files'          => $db->table('work_files')->countAllResults(),
            'owners'              => $db->table('owners')->countAllResults(),
            'licenses'            => $db->table('licenses')->countAllResults(),
            'licensees'           => $db->table('licensees')->countAllResults(),
            'usage_reports'       => $db->table('usage_reports')->countAllResults(),
            'infringement_cases'  => $db->table('infringement_cases')->countAllResults(),
            'audit_logs'          => $db->table('audit_logs')->countAllResults(),
        ];

        $roleSlugs = session()->get('auth_role_slugs') ?? [];
        $roleLabel = is_array($roleSlugs) && $roleSlugs !== []
            ? implode(', ', $roleSlugs)
            : '—';

        $currentUser = [
            'name'  => (string) session()->get('auth_name'),
            'email' => (string) session()->get('auth_email'),
            'role'  => $roleLabel,
        ];

        return view('main/dashboard', [
            'pageTitle'   => 'Dashboard',
            'currentPage' => 'dashboard',
            'currentUser' => $currentUser,
            'counts'      => $counts,
        ]);
    }
}
