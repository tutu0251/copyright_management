<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class Permission_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'permissions';
		$this->soft_delete = FALSE;
		$this->default_order = 'slug ASC';
	}

	public function all_slugs()
	{
		$rows = $this->db->select('slug')->order_by('slug ASC')
			->get('permissions')->result_array();
		$out = array();
		foreach ($rows as $r) $out[] = $r['slug'];
		return $out;
	}
}

/* End of file permission_model.php */
