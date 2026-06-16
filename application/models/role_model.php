<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class Role_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'roles';
		$this->soft_delete = FALSE;
		$this->default_order = 'slug ASC';
	}

	public function get_by_slug($slug)
	{
		$row = $this->db->where('slug', $slug)->get('roles')->row_array();
		return $row ? $row : NULL;
	}

	public function list_with_counts()
	{
		return $this->db
			->select('r.*, COUNT(rp.permission_id) AS permission_count', FALSE)
			->from('roles r')
			->join('role_permissions rp', 'rp.role_id = r.id', 'left')
			->group_by('r.id')
			->order_by('r.slug ASC')
			->get()->result_array();
	}

	public function permission_slugs($role_id)
	{
		$rows = $this->db->select('p.slug')
			->from('role_permissions rp')
			->join('permissions p', 'p.id = rp.permission_id')
			->where('rp.role_id', (int) $role_id)
			->get()->result_array();
		$out = array();
		foreach ($rows as $r) $out[] = $r['slug'];
		return $out;
	}

	/** Replace a role's permission set with the given slugs. */
	public function set_permissions($role_id, $slugs)
	{
		$role_id = (int) $role_id;
		$this->db->where('role_id', $role_id)->delete('role_permissions');
		if (empty($slugs)) return;

		$ids = $this->db->select('id')->from('permissions')
			->where_in('slug', $slugs)->get()->result_array();
		if (empty($ids)) return;

		$batch = array();
		foreach ($ids as $row)
		{
			$batch[] = array('role_id' => $role_id, 'permission_id' => (int) $row['id']);
		}
		$this->db->insert_batch('role_permissions', $batch);
	}
}

/* End of file role_model.php */
