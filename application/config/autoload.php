<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
| Autoloaded resources. Database + session are needed on essentially every
| request (auth check + queries); helpers/url+form+auth are used app-wide.
*/

$autoload['packages'] = array();
$autoload['libraries'] = array('database', 'session');
$autoload['helper'] = array('url', 'form', 'auth');
$autoload['config'] = array();
$autoload['language'] = array();
$autoload['model'] = array();

/* End of file autoload.php */
