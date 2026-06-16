<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class User_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'users';
		$this->soft_delete = FALSE;   // users use is_active, not deleted_at
	}

	public function get_by_email($email)
	{
		$row = $this->db->where('email', strtolower(trim($email)))
			->get('users')->row_array();
		return $row ? $row : NULL;
	}

	public function create($email, $password_hash, $display_name)
	{
		return $this->insert(array(
			'email'         => strtolower(trim($email)),
			'password_hash' => $password_hash,
			'display_name'  => $display_name,
			'is_active'     => 1,
		));
	}

	public function set_active($id, $active)
	{
		return $this->update($id, array('is_active' => $active ? 1 : 0));
	}

	public function touch_login($id)
	{
		return $this->update($id, array('last_login_at' => $this->now()));
	}

	public function assign_role($user_id, $role_id)
	{
		// INSERT IGNORE-style: skip if the pair already exists.
		$exists = $this->db->where(array('user_id' => $user_id, 'role_id' => $role_id))
			->count_all_results('user_roles');
		if ( ! $exists)
		{
			$this->db->insert('user_roles', array('user_id' => $user_id, 'role_id' => $role_id));
		}
	}

	/** Distinct permission slugs the user holds across all their roles. */
	public function permission_slugs($user_id)
	{
		$rows = $this->db->distinct()->select('p.slug')
			->from('user_roles ur')
			->join('role_permissions rp', 'rp.role_id = ur.role_id')
			->join('permissions p', 'p.id = rp.permission_id')
			->where('ur.user_id', (int) $user_id)
			->get()->result_array();
		$out = array();
		foreach ($rows as $r) $out[] = $r['slug'];
		return $out;
	}

	/** Role rows (slug, name) for a user. */
	public function roles_of($user_id)
	{
		return $this->db->select('r.slug, r.name')
			->from('user_roles ur')
			->join('roles r', 'r.id = ur.role_id')
			->where('ur.user_id', (int) $user_id)
			->get()->result_array();
	}

	/** Users with a comma-joined list of role names for the admin table. */
	public function list_with_roles()
	{
		return $this->db
			->select("u.*, GROUP_CONCAT(r.name ORDER BY r.name SEPARATOR ', ') AS role_names", FALSE)
			->from('users u')
			->join('user_roles ur', 'ur.user_id = u.id', 'left')
			->join('roles r', 'r.id = ur.role_id', 'left')
			->group_by('u.id')
			->order_by('u.created_at DESC')
			->get()->result_array();
	}
}

/* End of file user_model.php */
