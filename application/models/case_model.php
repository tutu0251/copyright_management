<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class Case_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'infringement_cases';
	}

	private function _join_select()
	{
		$this->db->select('c.*, w.title AS work_title', FALSE)
			->from('infringement_cases c')
			->join('works w', 'w.id = c.work_id', 'left')
			->where('c.deleted_at IS NULL', NULL, FALSE);
	}

	public function list_decorated($limit = 500)
	{
		$this->_join_select();
		return $this->db->order_by('c.updated_at DESC')->limit((int) $limit)
			->get()->result_array();
	}

	public function get_decorated($id)
	{
		$this->_join_select();
		$row = $this->db->where('c.id', (int) $id)->get()->row_array();
		return $row ? $row : NULL;
	}

	public function set_status($id, $status)
	{
		return $this->update($id, array('case_status' => $status));
	}

	// --- case notes (was the embedded Case.notes[] array) -------------------
	public function notes($case_id)
	{
		return $this->db->select('n.*, u.display_name AS author_name', FALSE)
			->from('case_notes n')
			->join('users u', 'u.id = n.author_id', 'left')
			->where('n.case_id', (int) $case_id)
			->order_by('n.created_at DESC')
			->get()->result_array();
	}

	public function add_note($case_id, $author_id, $body)
	{
		$this->db->insert('case_notes', array(
			'case_id'    => (int) $case_id,
			'author_id'  => $author_id ? (int) $author_id : NULL,
			'body'       => $body,
			'created_at' => $this->now(),
		));
		return (int) $this->db->insert_id();
	}

	/** counts grouped by case_status for the dashboard donut. */
	public function counts_by_status()
	{
		return $this->db->select('case_status, COUNT(*) AS cnt', FALSE)
			->where('deleted_at IS NULL', NULL, FALSE)
			->group_by('case_status')
			->get('infringement_cases')->result_array();
	}
}

/* End of file case_model.php */
