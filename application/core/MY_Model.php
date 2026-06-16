<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Tiny soft-delete CRUD base over CodeIgniter's Query Builder. Each concrete
 * model sets $table and gets get/all/insert/update/soft_delete with automatic
 * created_at/updated_at timestamps and a `deleted_at IS NULL` filter — the same
 * conventions the old Mongo model layer (server/src/lib/model.js) provided.
 *
 * Model files `require_once` this before declaring their class, because CI 2.1.3
 * does not auto-load a MY_Model base for plain models.
 */
class MY_Model extends CI_Model {

	protected $table = '';
	protected $soft_delete = TRUE;
	protected $timestamps = TRUE;
	protected $default_order = 'updated_at DESC';

	public function __construct()
	{
		parent::__construct();
	}

	protected function now()
	{
		return date('Y-m-d H:i:s');
	}

	/** Apply the not-deleted filter when soft deletes are enabled. */
	protected function _active()
	{
		if ($this->soft_delete)
		{
			$this->db->where($this->table.'.deleted_at IS NULL', NULL, FALSE);
		}
	}

	public function get($id)
	{
		$this->db->from($this->table);
		$this->db->where($this->table.'.id', (int) $id);
		$this->_active();
		$row = $this->db->get()->row_array();
		return $row ? $row : NULL;
	}

	public function all($limit = 500, $order = NULL)
	{
		$this->db->from($this->table);
		$this->_active();
		$this->db->order_by($order ? $order : $this->default_order);
		if ($limit) $this->db->limit((int) $limit);
		return $this->db->get()->result_array();
	}

	public function insert($data)
	{
		if ($this->timestamps)
		{
			$now = $this->now();
			if ( ! isset($data['created_at'])) $data['created_at'] = $now;
			if ( ! isset($data['updated_at'])) $data['updated_at'] = $now;
		}
		$this->db->insert($this->table, $data);
		return (int) $this->db->insert_id();
	}

	public function update($id, $data)
	{
		if ($this->timestamps && ! isset($data['updated_at']))
		{
			$data['updated_at'] = $this->now();
		}
		$this->db->where('id', (int) $id);
		if ($this->soft_delete)
		{
			$this->db->where('deleted_at IS NULL', NULL, FALSE);
		}
		$this->db->update($this->table, $data);
		return $this->db->affected_rows();
	}

	public function soft_delete($id)
	{
		if ( ! $this->soft_delete)
		{
			$this->db->where('id', (int) $id)->delete($this->table);
			return $this->db->affected_rows();
		}
		return $this->update($id, array('deleted_at' => $this->now()));
	}

	public function count_active($where = array())
	{
		$this->db->from($this->table);
		$this->_active();
		if ( ! empty($where)) $this->db->where($where);
		return (int) $this->db->count_all_results();
	}
}

/* End of file MY_Model.php */
