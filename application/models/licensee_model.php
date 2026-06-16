<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class Licensee_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'licensees';
		$this->default_order = 'name ASC';
	}

	/** Map of id => name for select dropdowns. */
	public function options()
	{
		$rows = $this->db->select('id, name')
			->where('deleted_at IS NULL', NULL, FALSE)
			->order_by('name ASC')->get('licensees')->result_array();
		$out = array();
		foreach ($rows as $r) $out[(int) $r['id']] = $r['name'];
		return $out;
	}
}

/* End of file licensee_model.php */
