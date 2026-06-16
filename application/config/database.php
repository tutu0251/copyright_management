<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
| MySQL 5.x connection for Copyright Management (PHP 5.6 -> mysqli driver).
| Adjust username/password/database for your environment. To keep credentials
| out of git, you can create application/config/database.local.php returning an
| override array; it is gitignored. (Loading it is optional — by default this
| file is authoritative.)
*/

$active_group = 'default';
$active_record = TRUE;   // CI2 Query Builder ("Active Record") used by models.

$db['default']['hostname'] = '127.0.0.1';
$db['default']['username'] = 'root';
$db['default']['password'] = '';
$db['default']['database'] = 'copyright_management';
$db['default']['dbdriver'] = 'mysqli';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = FALSE;
$db['default']['db_debug'] = TRUE;       // set FALSE in production
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

// Optional local override (gitignored). Lets a dev set creds without touching
// this committed file.
if (file_exists(APPPATH.'config/database.local.php'))
{
	include(APPPATH.'config/database.local.php');
}

/* End of file database.php */
