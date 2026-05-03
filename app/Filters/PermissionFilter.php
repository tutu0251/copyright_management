<?php

declare(strict_types=1);

namespace App\Filters;

use App\Services\PermissionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['auth', 'permission']);

        if (! auth_logged_in()) {
            return redirect()->to(site_url('login'));
        }

        if (! PermissionService::schemaReady()) {
            return null;
        }

        $slugs = is_array($arguments) ? $arguments : [];
        $slugs = array_values(array_filter(array_map(static fn ($s) => trim((string) $s), $slugs), static fn ($s) => $s !== ''));
        if ($slugs === []) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Access control is not configured for this route.');
        }

        $ok = count($slugs) === 1
            ? user_can($slugs[0])
            : user_can_any($slugs);

        if (! $ok) {
            return redirect()->to(site_url('dashboard'))->with('error', 'You do not have permission to access that page.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
