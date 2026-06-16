<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class Audit_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'audit_logs';
		$this->soft_delete = FALSE;
		$this->timestamps = FALSE;   // only created_at
	}

	public function log($action_type, $entity_type = '', $entity_id = '', $metadata = array(), $actor_id = NULL)
	{
		$this->db->insert('audit_logs', array(
			'action_type' => $action_type,
			'entity_type' => $entity_type !== NULL ? $entity_type : '',
			'entity_id'   => $entity_id !== NULL ? (string) $entity_id : '',
			'actor_id'    => $actor_id ? (int) $actor_id : NULL,
			'metadata'    => empty($metadata) ? NULL : json_encode($metadata),
			'created_at'  => date('Y-m-d H:i:s'),
		));
		return (int) $this->db->insert_id();
	}

	public function recent($limit = 50)
	{
		return $this->db->select('a.*, u.display_name AS actor_name, u.email AS actor_email', FALSE)
			->from('audit_logs a')
			->join('users u', 'u.id = a.actor_id', 'left')
			->order_by('a.created_at DESC')
			->limit((int) $limit)
			->get()->result_array();
	}

	public function count_today()
	{
		return (int) $this->db->where('created_at >=', date('Y-m-d 00:00:00'))
			->count_all_results('audit_logs');
	}
}

/* End of file audit_model.php */
