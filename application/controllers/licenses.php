<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Licenses extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('license_model');
		$this->load->model('work_model');
		$this->load->model('licensee_model');
	}

	public function index()
	{
		$this->require_perm('licenses.view');
		$this->render('licenses/index', array('items' => $this->license_model->list_decorated()), 'Licenses', 'licenses');
	}

	public function create()
	{
		$this->require_perm('licenses.create');
		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$id = $this->license_model->insert($this->_payload());
			audit_log('create', 'license', $id);
			$this->session->set_flashdata('ok', 'License created.');
			redirect('licenses');
			return;
		}
		$this->_form(NULL, 'create');
	}

	public function edit($id)
	{
		$this->require_perm('licenses.update');
		$item = $this->license_model->get($id);
		if ( ! $item) show_404();

		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$this->license_model->update($id, $this->_payload());
			audit_log('update', 'license', $id);
			$this->session->set_flashdata('ok', 'License updated.');
			redirect('licenses');
			return;
		}
		$this->_form($item, 'edit');
	}

	public function delete($id)
	{
		$this->require_perm('licenses.delete');
		if ($this->license_model->soft_delete($id))
		{
			audit_log('delete', 'license', $id);
			$this->session->set_flashdata('ok', 'License removed.');
		}
		redirect('licenses');
	}

	private function _form($item, $mode)
	{
		$this->render('licenses/form', array(
			'item'      => $item,
			'mode'      => $mode,
			'works'     => $this->work_model->options(),
			'licensees' => $this->licensee_model->options(),
		), $mode === 'edit' ? 'Edit license' : 'New license', 'licenses');
	}

	private function _payload()
	{
		$start = trim($this->input->post('start_date'));
		$end   = trim($this->input->post('end_date'));
		return array(
			'work_id'        => $this->input->post('work_id') ? (int) $this->input->post('work_id') : NULL,
			'licensee_id'    => $this->input->post('licensee_id') ? (int) $this->input->post('licensee_id') : NULL,
			'license_type'   => $this->input->post('license_type') ? $this->input->post('license_type') : 'non_exclusive',
			'license_status' => $this->input->post('license_status') ? $this->input->post('license_status') : 'draft',
			'payment_status' => $this->input->post('payment_status') ? $this->input->post('payment_status') : 'unpaid',
			'fee_amount'     => (float) $this->input->post('fee_amount'),
			'territory'      => trim($this->input->post('territory')),
			'start_date'     => $start !== '' ? $start : NULL,
			'end_date'       => $end !== '' ? $end : NULL,
		);
	}
}

/* End of file licenses.php */
