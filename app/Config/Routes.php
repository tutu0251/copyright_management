<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', static function () {
    return redirect()->to(site_url('mockup'));
});

$routes->group('mockup', static function ($routes) {
    $routes->get('/', 'Mockup::dashboard');
    $routes->get('works', 'Mockup::worksRedirect');
    $routes->get('assets', 'Mockup::assets');
    $routes->get('register', 'Mockup::register');
    $routes->get('work/(:segment)', 'Mockup::workDetail/$1');
    $routes->get('ownership', 'Mockup::ownership');
    $routes->get('licenses', 'Mockup::licenses');
    $routes->get('license/(:segment)', 'Mockup::licenseDetail/$1');
    $routes->get('usage-reports', 'Mockup::usageReports');
    $routes->get('monitoring', 'Mockup::monitoring');
    $routes->get('reports', 'Mockup::reports');
    $routes->get('cases', 'Mockup::cases');
    $routes->get('case/(:segment)', 'Mockup::caseDetail/$1');
    $routes->get('settings', 'Mockup::settings');
});
