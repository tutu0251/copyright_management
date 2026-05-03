<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\App;

class LocaleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): void
    {
        /** @var App $app */
        $app       = config(App::class);
        $default   = $app->defaultLocale;
        $supported = $app->supportedLocales;

        $session = session();
        $chosen  = (string) $session->get('app_locale');
        if ($chosen === '' || ! in_array($chosen, $supported, true)) {
            $chosen = $default;
        }

        $request->setLocale($chosen);
        helper('locale');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
