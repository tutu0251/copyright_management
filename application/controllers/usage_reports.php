<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Usage_reports extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('usagereport_model');
		$this->load->model('work_model');
	}

	public function index()
	{
		$this->require_perm('usage_reports.view');
		$this->render('usage_reports/index', array('items' => $this->usagereport_model->list_decorated()), 'Usage reports', 'usage_reports');
	}

	public function create()
	{
		$this->require_perm('usage_reports.create');
		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$id = $this->usagereport_model->insert($this->_payload());
			audit_log('create', 'usage_report', $id);
			$this->session->set_flashdata('ok', 'Usage report logged.');
			redirect('usage_reports');
			return;
		}
		$this->_form(NULL, 'create');
	}

	public function edit($id)
	{
		$this->require_perm('usage_reports.update');
		$item = $this->usagereport_model->get($id);
		if ( ! $item) show_404();

		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$this->usagereport_model->update($id, $this->_payload());
			audit_log('update', 'usage_report', $id);
			$this->session->set_flashdata('ok', 'Usage report updated.');
			redirect('usage_reports');
			return;
		}
		$this->_form($item, 'edit');
	}

	public function delete($id)
	{
		$this->require_perm('usage_reports.delete');
		if ($this->usagereport_model->soft_delete($id))
		{
			audit_log('delete', 'usage_report', $id);
			$this->session->set_flashdata('ok', 'Usage report removed.');
		}
		redirect('usage_reports');
	}

	private function _form($item, $mode)
	{
		$this->render('usage_reports/form', array(
			'item'  => $item,
			'mode'  => $mode,
			'works' => $this->work_model->options(),
		), $mode === 'edit' ? 'Edit usage report' : 'New usage report', 'usage_reports');
	}

	private function _payload()
	{
		$detected = trim($this->input->post('detected_at'));
		$detected = $detected !== '' ? str_replace('T', ' ', $detected) : date('Y-m-d H:i:s');
		return array(
			'work_id'         => $this->input->post('work_id') ? (int) $this->input->post('work_id') : NULL,
			'usage_type'      => $this->input->post('usage_type') ? $this->input->post('usage_type') : 'suspected',
			'detected_source' => trim($this->input->post('detected_source')),
			'detected_at'     => $detected,
			'notes'           => trim($this->input->post('notes')),
		);
	}
}

/* End of file usage_reports.php */
