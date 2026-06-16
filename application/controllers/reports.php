<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('work_model', 'license_model', 'usagereport_model', 'case_model'));
	}

	public function index()
	{
		$this->require_perm('reports.view');
		$this->render('reports/index', array(
			'summary' => array(
				'Works'         => $this->work_model->count_active(),
				'Licenses'      => $this->license_model->count_active(),
				'Usage reports' => $this->usagereport_model->count_active(),
				'Cases'         => $this->case_model->count_active(),
			),
			'works'    => $this->work_model->all(500, 'title ASC'),
			'licenses' => $this->license_model->list_decorated(),
		), 'Reports', 'reports');
	}
}

/* End of file reports.php */
