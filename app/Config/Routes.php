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

    $routes->get('works', 'Works::index');
    $routes->get('works/create', 'Works::create');
    $routes->post('works', 'Works::store');
    $routes->get('works/(:num)', 'Works::show/$1');
    $routes->get('works/(:num)/edit', 'Works::edit/$1');
    $routes->post('works/(:num)/update', 'Works::update/$1');
    $routes->post('works/(:num)/delete', 'Works::delete/$1');
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
