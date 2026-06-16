<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Activities extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('audit_model');
	}

	public function index()
	{
		$this->require_perm('activities.view');
		$limit = (int) $this->input->get('limit');
		if ($limit <= 0 || $limit > 200) $limit = 100;
		$this->render('activities/index', array(
			'items' => $this->audit_model->recent($limit),
		), 'Activity', 'activities');
	}
}

/* End of file activities.php */
