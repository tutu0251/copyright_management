<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * View/controller helpers for the current session's identity and permissions.
 * Permissions are resolved per-request by Secure_Controller and exposed on the
 * controller instance; these helpers read them so views (e.g. the sidebar) can
 * show/hide links — mirroring the React `can(perm)` checks in Layout.jsx.
 */

if ( ! function_exists('html_escape'))
{
	/**
	 * Backport of CodeIgniter's html_escape() (added in 2.2.0) so views work on
	 * CI 2.1.3. Escapes a string (or array of strings) for safe HTML output.
	 */
	function html_escape($var)
	{
		if (is_array($var))
		{
			return array_map('html_escape', $var);
		}
		return htmlspecialchars($var === NULL ? '' : $var, ENT_QUOTES, 'UTF-8');
	}
}

if ( ! function_exists('current_user'))
{
	function current_user()
	{
		$CI =& get_instance();
		return isset($CI->current_user) ? $CI->current_user : NULL;
	}
}

if ( ! function_exists('has_perm'))
{
	function has_perm($slug)
	{
		$CI =& get_instance();
		return isset($CI->perms) && in_array($slug, $CI->perms, TRUE);
	}
}

if ( ! function_exists('audit_log'))
{
	/**
	 * Append an audit-trail entry. $metadata is JSON-encoded into a TEXT column
	 * (MySQL 5.x has no JSON type). Mirrors services/auditLog.js.
	 */
	function audit_log($action_type, $entity_type = '', $entity_id = '', $metadata = array(), $actor_id = NULL)
	{
		$CI =& get_instance();
		$CI->load->model('audit_model');
		if ($actor_id === NULL && isset($CI->current_user['id']))
		{
			$actor_id = $CI->current_user['id'];
		}
		$CI->audit_model->log($action_type, $entity_type, $entity_id, $metadata, $actor_id);
	}
}

if ( ! function_exists('e'))
{
	/** Short HTML-escape for views. */
	function e($value)
	{
		return html_escape($value === NULL ? '' : $value);
	}
}

if ( ! function_exists('human_size'))
{
	/** Format a byte count as a human-readable string. */
	function human_size($bytes)
	{
		$bytes = (float) $bytes;
		if ($bytes <= 0) return '0 B';
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$i = (int) floor(log($bytes, 1024));
		if ($i >= count($units)) $i = count($units) - 1;
		return round($bytes / pow(1024, $i), ($i === 0 ? 0 : 1)).' '.$units[$i];
	}
}

if ( ! function_exists('badge'))
{
	/**
	 * Render a colored status badge. Maps common status keywords to a color so
	 * works/licenses/cases read consistently.
	 */
	function badge($value)
	{
		$v = strtolower(trim((string) $value));
		$green = array('registered', 'active', 'paid', 'resolved', 'approved');
		$amber = array('draft', 'pending', 'partial', 'investigating', 'suspected', 'medium', 'open');
		$red   = array('cancelled', 'unpaid', 'rejected', 'infringement', 'high', 'expired');
		$cls = 'badge--gray';
		if (in_array($v, $green, TRUE)) $cls = 'badge--green';
		elseif (in_array($v, $red, TRUE)) $cls = 'badge--red';
		elseif (in_array($v, $amber, TRUE)) $cls = 'badge--amber';
		return '<span class="badge '.$cls.'">'.html_escape($value === NULL || $value === '' ? '—' : $value).'</span>';
	}
}

/* End of file auth_helper.php */
