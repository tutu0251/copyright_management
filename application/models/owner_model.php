<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH.'core/MY_Model.php';

class Owner_model extends MY_Model {

	public function __construct()
	{
		parent::__construct();
		$this->table = 'owners';
		$this->default_order = 'legal_name ASC';
	}
}

/* End of file owner_model.php */
