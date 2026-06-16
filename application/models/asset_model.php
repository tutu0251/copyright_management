<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class Asset_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'work_assets';
	}

	public function for_work($work_id)
	{
		return $this->db->from('work_assets')
			->where('work_id', (int) $work_id)
			->where('deleted_at IS NULL', NULL, FALSE)
			->order_by('created_at DESC')
			->get()->result_array();
	}
}

/* End of file asset_model.php */
