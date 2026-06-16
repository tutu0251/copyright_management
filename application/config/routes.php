<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$route['default_controller'] = 'dashboard';
$route['404_override'] = '';

// Friendly aliases that match the old SPA paths / sidebar links.
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['register'] = 'auth/register';

$route['usage-reports'] = 'usage_reports/index';
$route['usage-reports/(:any)'] = 'usage_reports/$1';
$route['settings/roles'] = 'roles/index';
$route['settings/roles/(:any)'] = 'roles/$1';

/* End of file routes.php */
