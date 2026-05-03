<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Config\App;

class Language extends BaseController
{
    /**
     * GET /lang/{en|ja} — store locale in session and redirect back.
     */
    public function set(string $locale): ResponseInterface
    {
        /** @var App $app */
        $app       = config(App::class);
        $supported = $app->supportedLocales;

        if (! in_array($locale, $supported, true)) {
            $locale = $app->defaultLocale;
        }

        session()->set('app_locale', $locale);

        return redirect()->back();
    }
}
