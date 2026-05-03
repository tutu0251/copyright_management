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
    $routes->get('dashboard', 'Dashboard::index', ['filter' => 'permission:dashboard.view']);
    $routes->get('activities', 'Activities::index', ['filter' => 'permission:activities.view']);
    $routes->post('logout', 'Auth::logout');

    $routes->get('settings/roles', 'SettingsRoles::index', ['filter' => 'permission:settings.manage']);
    $routes->get('settings/roles/(:num)/permissions', 'SettingsRoles::editPermissions/$1', ['filter' => 'permission:settings.manage']);
    $routes->post('settings/roles/(:num)/permissions', 'SettingsRoles::updatePermissions/$1', ['filter' => 'permission:settings.manage']);

    $routes->get('owners', 'Owners::index', ['filter' => 'permission:owners.view']);
    $routes->get('owners/create', 'Owners::create', ['filter' => 'permission:owners.create']);
    $routes->post('owners', 'Owners::store', ['filter' => 'permission:owners.create']);
    $routes->get('owners/(:num)', 'Owners::show/$1', ['filter' => 'permission:owners.view']);
    $routes->get('owners/(:num)/edit', 'Owners::edit/$1', ['filter' => 'permission:owners.update']);
    $routes->post('owners/(:num)/update', 'Owners::update/$1', ['filter' => 'permission:owners.update']);
    $routes->post('owners/(:num)/delete', 'Owners::delete/$1', ['filter' => 'permission:owners.delete']);

    $routes->get('works/(:num)/owners', 'WorkOwners::index/$1', ['filter' => 'permission:works.view']);
    $routes->post('works/(:num)/owners', 'WorkOwners::store/$1', ['filter' => 'permission:owners.update']);
    $routes->post('works/(:num)/owners/(:num)/delete', 'WorkOwners::unlink/$1/$2', ['filter' => 'permission:owners.update']);

    $routes->get('works', 'Works::index', ['filter' => 'permission:works.view']);
    $routes->get('works/create', 'Works::create', ['filter' => 'permission:works.create']);
    $routes->post('works', 'Works::store', ['filter' => 'permission:works.create']);
    $routes->get('works/(:num)', 'Works::show/$1', ['filter' => 'permission:works.view']);
    $routes->get('works/(:num)/edit', 'Works::edit/$1', ['filter' => 'permission:works.update']);
    $routes->post('works/(:num)/update', 'Works::update/$1', ['filter' => 'permission:works.update']);
    $routes->post('works/(:num)/delete', 'Works::delete/$1', ['filter' => 'permission:works.delete']);

    $routes->get('licensees', 'Licensees::index', ['filter' => 'permission:licensees.view']);
    $routes->get('licensees/create', 'Licensees::create', ['filter' => 'permission:licensees.create']);
    $routes->post('licensees', 'Licensees::store', ['filter' => 'permission:licensees.create']);
    $routes->get('licensees/(:num)', 'Licensees::show/$1', ['filter' => 'permission:licensees.view']);
    $routes->get('licensees/(:num)/edit', 'Licensees::edit/$1', ['filter' => 'permission:licensees.update']);
    $routes->post('licensees/(:num)/update', 'Licensees::update/$1', ['filter' => 'permission:licensees.update']);
    $routes->post('licensees/(:num)/delete', 'Licensees::delete/$1', ['filter' => 'permission:licensees.delete']);

    $routes->get('licenses', 'Licenses::index', ['filter' => 'permission:licenses.view']);
    $routes->get('licenses/create', 'Licenses::create', ['filter' => 'permission:licenses.create']);
    $routes->post('licenses', 'Licenses::store', ['filter' => 'permission:licenses.create']);
    $routes->get('licenses/(:num)', 'Licenses::show/$1', ['filter' => 'permission:licenses.view']);
    $routes->get('licenses/(:num)/edit', 'Licenses::edit/$1', ['filter' => 'permission:licenses.update']);
    $routes->post('licenses/(:num)/update', 'Licenses::update/$1', ['filter' => 'permission:licenses.update']);
    $routes->post('licenses/(:num)/delete', 'Licenses::delete/$1', ['filter' => 'permission:licenses.delete']);

    $routes->get('usage-reports/(:num)/evidence', 'UsageReports::evidence/$1', ['filter' => 'permission:usage_reports.view']);
    $routes->get('usage-reports', 'UsageReports::index', ['filter' => 'permission:usage_reports.view']);
    $routes->get('usage-reports/create', 'UsageReports::create', ['filter' => 'permission:usage_reports.create']);
    $routes->post('usage-reports', 'UsageReports::store', ['filter' => 'permission:usage_reports.create']);
    $routes->get('usage-reports/(:num)/edit', 'UsageReports::edit/$1', ['filter' => 'permission:usage_reports.update']);
    $routes->get('usage-reports/(:num)', 'UsageReports::show/$1', ['filter' => 'permission:usage_reports.view']);
    $routes->post('usage-reports/(:num)/update', 'UsageReports::update/$1', ['filter' => 'permission:usage_reports.update']);
    $routes->post('usage-reports/(:num)/delete', 'UsageReports::delete/$1', ['filter' => 'permission:usage_reports.delete']);
    $routes->post('usage-reports/(:num)/mark-authorized', 'UsageReports::markAuthorized/$1', ['filter' => 'permission:usage_reports.update']);
    $routes->post('usage-reports/(:num)/mark-infringement', 'UsageReports::markInfringement/$1', ['filter' => 'permission:usage_reports.update']);
    $routes->post('usage-reports/(:num)/escalate-case', 'UsageReports::escalateCase/$1', ['filter' => 'permission:cases.create']);

    $routes->get('cases', 'Cases::index', ['filter' => 'permission:cases.view']);
    $routes->get('cases/create', 'Cases::create', ['filter' => 'permission:cases.create']);
    $routes->post('cases/create', 'Cases::store', ['filter' => 'permission:cases.create']);
    $routes->get('cases/(:num)', 'Cases::show/$1', ['filter' => 'permission:cases.view']);
    $routes->get('cases/(:num)/edit', 'Cases::edit/$1', ['filter' => 'permission:cases.update']);
    $routes->post('cases/(:num)/update', 'Cases::update/$1', ['filter' => 'permission:cases.update']);
    $routes->post('cases/(:num)/delete', 'Cases::delete/$1', ['filter' => 'permission:cases.delete']);
    $routes->post('cases/(:num)/status', 'Cases::updateStatus/$1', ['filter' => 'permission:cases.status_update']);
    $routes->post('cases/(:num)/evidence', 'Cases::uploadEvidence/$1', ['filter' => 'permission:cases.update']);
    $routes->post('cases/(:num)/note', 'Cases::addNote/$1', ['filter' => 'permission:cases.update']);
    $routes->get('cases/(:num)/files/(:num)', 'Cases::evidenceFile/$1/$2', ['filter' => 'permission:cases.view']);
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
