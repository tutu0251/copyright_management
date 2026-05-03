<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', static function () {
    return redirect()->to(site_url('dashboard'));
});

$routes->group('', ['filter' => 'guest'], static function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::attemptLogin');
});

$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->post('logout', 'Auth::logout');

    $routes->get('owners', 'Owners::index');
    $routes->get('owners/create', 'Owners::create');
    $routes->post('owners', 'Owners::store');
    $routes->get('owners/(:num)', 'Owners::show/$1');
    $routes->get('owners/(:num)/edit', 'Owners::edit/$1');
    $routes->post('owners/(:num)/update', 'Owners::update/$1');
    $routes->post('owners/(:num)/delete', 'Owners::delete/$1');

    $routes->get('works/(:num)/owners', 'WorkOwners::index/$1');
    $routes->post('works/(:num)/owners', 'WorkOwners::store/$1');
    $routes->post('works/(:num)/owners/(:num)/delete', 'WorkOwners::unlink/$1/$2');

    $routes->get('works', 'Works::index');
    $routes->get('works/create', 'Works::create');
    $routes->post('works', 'Works::store');
    $routes->get('works/(:num)', 'Works::show/$1');
    $routes->get('works/(:num)/edit', 'Works::edit/$1');
    $routes->post('works/(:num)/update', 'Works::update/$1');
    $routes->post('works/(:num)/delete', 'Works::delete/$1');

    $routes->get('licensees', 'Licensees::index');
    $routes->get('licensees/create', 'Licensees::create');
    $routes->post('licensees', 'Licensees::store');
    $routes->get('licensees/(:num)', 'Licensees::show/$1');
    $routes->get('licensees/(:num)/edit', 'Licensees::edit/$1');
    $routes->post('licensees/(:num)/update', 'Licensees::update/$1');
    $routes->post('licensees/(:num)/delete', 'Licensees::delete/$1');

    $routes->get('licenses', 'Licenses::index');
    $routes->get('licenses/create', 'Licenses::create');
    $routes->post('licenses', 'Licenses::store');
    $routes->get('licenses/(:num)', 'Licenses::show/$1');
    $routes->get('licenses/(:num)/edit', 'Licenses::edit/$1');
    $routes->post('licenses/(:num)/update', 'Licenses::update/$1');
    $routes->post('licenses/(:num)/delete', 'Licenses::delete/$1');

    $routes->get('usage-reports/(:num)/evidence', 'UsageReports::evidence/$1');
    $routes->get('usage-reports', 'UsageReports::index');
    $routes->get('usage-reports/create', 'UsageReports::create');
    $routes->post('usage-reports', 'UsageReports::store');
    $routes->get('usage-reports/(:num)/edit', 'UsageReports::edit/$1');
    $routes->get('usage-reports/(:num)', 'UsageReports::show/$1');
    $routes->post('usage-reports/(:num)/update', 'UsageReports::update/$1');
    $routes->post('usage-reports/(:num)/delete', 'UsageReports::delete/$1');
    $routes->post('usage-reports/(:num)/mark-authorized', 'UsageReports::markAuthorized/$1');
    $routes->post('usage-reports/(:num)/mark-infringement', 'UsageReports::markInfringement/$1');
    $routes->post('usage-reports/(:num)/escalate-case', 'UsageReports::escalateCase/$1');
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
