<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('work_model', 'owner_model', 'license_model',
			'usagereport_model', 'case_model', 'audit_model'));
	}

	public function index()
	{
		$this->require_perm('dashboard.view');

		$stats = array(
			array('Total works',                $this->work_model->count_active()),
			array('Total owners',               $this->owner_model->count_active()),
			array('Total licenses',             $this->license_model->count_active()),
			array('Active licenses',            $this->license_model->count_current()),
			array('Expiring within 30 days',    $this->license_model->count_expiring(30)),
			array('Revenue (paid fees)',        '$'.number_format($this->license_model->sum_fees(array('paid')), 2)),
			array('Unpaid license fees',        '$'.number_format($this->license_model->sum_fees(array('unpaid', 'partial')), 2)),
			array('Usage reports',              $this->usagereport_model->count_active()),
			array('Infringement reports',       $this->usagereport_model->count_active(array('usage_type' => 'infringement'))),
			array('Open cases',                 $this->_open_cases()),
			array('Resolved cases',             $this->case_model->count_active(array('case_status' => 'resolved'))),
		);

		// 12-month "works registered" series.
		$axis = array();
		for ($i = 11; $i >= 0; $i--)
		{
			$t = strtotime('first day of -'.$i.' month');
			$axis[] = array('ym' => date('Y-m', $t), 'label' => date('M Y', $t));
		}
		$since = $axis[0]['ym'].'-01 00:00:00';
		$by_month = array();
		foreach ($this->work_model->counts_by_month($since) as $r) $by_month[$r['ym']] = (int) $r['cnt'];
		$series = array();
		foreach ($axis as $a) $series[] = array('label' => $a['label'], 'value' => isset($by_month[$a['ym']]) ? $by_month[$a['ym']] : 0);

		$this->render('dashboard/index', array(
			'stats'         => $stats,
			'series'        => $series,
			'cases_status'  => $this->case_model->counts_by_status(),
			'pinned'        => $this->work_model->recent(5),
			'recent_usage'  => $this->usagereport_model->recent_detections(date('Y-m-d H:i:s', strtotime('-30 days')), 8),
			'activity'      => $this->audit_model->recent(12),
		), 'Dashboard', 'dashboard');
	}

	private function _open_cases()
	{
		return (int) $this->db->from('infringement_cases')
			->where('deleted_at IS NULL', NULL, FALSE)
			->where_not_in('case_status', array('resolved', 'rejected', 'closed'))
			->count_all_results();
	}
}

/* End of file dashboard.php */
