<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class License_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'licenses';
	}

	private function _join_select()
	{
		$this->db->select('l.*, w.title AS work_title, lc.name AS licensee_name', FALSE)
			->from('licenses l')
			->join('works w', 'w.id = l.work_id', 'left')
			->join('licensees lc', 'lc.id = l.licensee_id', 'left')
			->where('l.deleted_at IS NULL', NULL, FALSE);
	}

	public function list_decorated($limit = 500)
	{
		$this->_join_select();
		return $this->db->order_by('l.updated_at DESC')->limit((int) $limit)
			->get()->result_array();
	}

	public function get_decorated($id)
	{
		$this->_join_select();
		$row = $this->db->where('l.id', (int) $id)->get()->row_array();
		return $row ? $row : NULL;
	}

	/** Licenses that are not draft/cancelled and not past their end date. */
	public function count_current()
	{
		$today = date('Y-m-d');
		return (int) $this->db->from('licenses')
			->where('deleted_at IS NULL', NULL, FALSE)
			->where_not_in('license_status', array('draft', 'cancelled'))
			->where("(end_date IS NULL OR end_date >= '".$this->db->escape_str($today)."')", NULL, FALSE)
			->count_all_results();
	}

	/** Current licenses expiring within $days days. */
	public function count_expiring($days = 30)
	{
		$today = date('Y-m-d');
		$limit = date('Y-m-d', strtotime('+'.(int) $days.' days'));
		return (int) $this->db->from('licenses')
			->where('deleted_at IS NULL', NULL, FALSE)
			->where_not_in('license_status', array('draft', 'cancelled'))
			->where('end_date >=', $today)
			->where('end_date <=', $limit)
			->count_all_results();
	}

	/** Sum of fee_amount across licenses with any of the given payment statuses. */
	public function sum_fees($payment_statuses)
	{
		$row = $this->db->select('COALESCE(SUM(fee_amount), 0) AS total', FALSE)
			->from('licenses')
			->where('deleted_at IS NULL', NULL, FALSE)
			->where_in('payment_status', $payment_statuses)
			->get()->row_array();
		return (float) $row['total'];
	}
}

/* End of file license_model.php */
