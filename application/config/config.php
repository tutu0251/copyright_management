<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
| CodeIgniter 2.1.3 application configuration for Copyright Management.
| Trimmed to the keys this app uses; defaults preserved where it doesn't matter.
*/

// Set to the URL the app is served from, e.g. http://localhost:8080/
// Leave blank to let CI guess; setting it explicitly avoids surprises.
$config['base_url'] = '';

// Clean URLs: index.php is removed by the root .htaccess.
$config['index_page'] = '';

$config['uri_protocol'] = 'AUTO';
$config['url_suffix'] = '';

$config['language'] = 'english';
$config['charset'] = 'UTF-8';

// Autoloaded hooks are off; we use core controller extension instead.
$config['enable_hooks'] = FALSE;
$config['subclass_prefix'] = 'MY_';

// Permit the characters our slugs / emails / query strings need.
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-@';
$config['enable_query_strings'] = FALSE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd';

$config['allow_get_array'] = TRUE;

$config['log_threshold'] = 1;          // 0=off, 1=errors only
$config['log_path'] = '';
$config['log_date_format'] = 'Y-m-d H:i:s';

$config['cache_path'] = '';

// IMPORTANT: replace with a 32+ char random string in any shared environment.
// Required by the encrypted session cookie below.
$config['encryption_key'] = 'CHANGE_ME_to_a_long_random_32char_key_please';

/*
| Sessions — native CI2 cookie sessions (encrypted). Auth stores user_id + the
| user's permission slugs here (see Auth controller / Secure_Controller).
*/
$config['sess_cookie_name'] = 'cm_session';
$config['sess_expiration'] = 7200;
$config['sess_expire_on_close'] = FALSE;
$config['sess_encrypt_cookie'] = TRUE;
$config['sess_use_database'] = FALSE;
$config['sess_table_name'] = 'ci_sessions';
$config['sess_match_ip'] = FALSE;
$config['sess_match_useragent'] = TRUE;
$config['sess_time_to_update'] = 300;

$config['cookie_prefix'] = '';
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_secure'] = FALSE;

$config['global_xss_filtering'] = FALSE;
$config['csrf_protection'] = FALSE;   // forms use CI form helper; enable in prod
$config['csrf_token_name'] = 'csrf_cm';
$config['csrf_cookie_name'] = 'csrf_cm_cookie';
$config['csrf_expire'] = 7200;

$config['compress_output'] = FALSE;
$config['time_reference'] = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';

/* End of file config.php */
