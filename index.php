<?php
/**
 * CodeIgniter 2.1.3 front controller for the Copyright Management app.
 *
 * Targets PHP 5.6.2. The framework core lives in ./system (drop in the official
 * CodeIgniter 2.1.3 release) and application code in ./application.
 */

define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');

switch (ENVIRONMENT)
{
	case 'development':
		// CodeIgniter 2.1.3 predates PHP 5.4+ by-reference semantics and emits
		// benign E_NOTICE/E_STRICT from its core on PHP 5.6; exclude those so
		// they don't corrupt output. Real warnings/errors still show.
		error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		break;
	case 'testing':
	case 'production':
		error_reporting(0);
		break;
	default:
		exit('The application environment is not set correctly.');
}

$system_path = 'system';
$application_folder = 'application';

// --- Resolve paths (verbatim from the stock CI 2.1.3 index.php) --------------
if (realpath($system_path) !== FALSE)
{
	$system_path = realpath($system_path).'/';
}
$system_path = rtrim($system_path, '/').'/';

if ( ! is_dir($system_path))
{
	exit('Your system folder path does not appear to be set correctly. '
		.'Drop the CodeIgniter 2.1.3 "system" folder next to index.php. '
		.'Please open the following file and correct this: '.pathinfo(__FILE__, PATHINFO_BASENAME));
}

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('EXT', '.php');
define('BASEPATH', str_replace("\\", "/", $system_path));
define('FCPATH', str_replace(SELF, '', __FILE__));
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

if (is_dir($application_folder))
{
	define('APPPATH', $application_folder.'/');
}
else
{
	if ( ! is_dir(BASEPATH.$application_folder.'/'))
	{
		exit('Your application folder path does not appear to be set correctly.');
	}
	define('APPPATH', BASEPATH.$application_folder.'/');
}

require_once BASEPATH.'core/CodeIgniter'.EXT;
