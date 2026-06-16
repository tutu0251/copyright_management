<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class Usagereport_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'usage_reports';
	}

	private function _join_select()
	{
		$this->db->select('u.*, w.title AS work_title', FALSE)
			->from('usage_reports u')
			->join('works w', 'w.id = u.work_id', 'left')
			->where('u.deleted_at IS NULL', NULL, FALSE);
	}

	public function list_decorated($limit = 500)
	{
		$this->_join_select();
		return $this->db->order_by('u.updated_at DESC')->limit((int) $limit)
			->get()->result_array();
	}

	public function get_decorated($id)
	{
		$this->_join_select();
		$row = $this->db->where('u.id', (int) $id)->get()->row_array();
		return $row ? $row : NULL;
	}

	/** Recent detections within the last $days days (dashboard widget). */
	public function recent_detections($since, $limit = 12)
	{
		$this->_join_select();
		return $this->db->where('u.detected_at >=', $since)
			->order_by('u.detected_at DESC')->limit((int) $limit)
			->get()->result_array();
	}
}

/* End of file usagereport_model.php */
