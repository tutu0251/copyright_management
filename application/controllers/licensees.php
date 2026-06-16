<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Licensees extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('licensee_model');
	}

	public function index()
	{
		$this->require_perm('licensees.view');
		$this->render('licensees/index', array('items' => $this->licensee_model->all()), 'Licensees', 'licensees');
	}

	public function create()
	{
		$this->require_perm('licensees.create');
		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$id = $this->licensee_model->insert($this->_payload());
			audit_log('create', 'licensee', $id);
			$this->session->set_flashdata('ok', 'Licensee created.');
			redirect('licensees');
			return;
		}
		$this->render('licensees/form', array('item' => NULL, 'mode' => 'create'), 'New licensee', 'licensees');
	}

	public function edit($id)
	{
		$this->require_perm('licensees.update');
		$item = $this->licensee_model->get($id);
		if ( ! $item) show_404();

		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$this->licensee_model->update($id, $this->_payload());
			audit_log('update', 'licensee', $id);
			$this->session->set_flashdata('ok', 'Licensee updated.');
			redirect('licensees');
			return;
		}
		$this->render('licensees/form', array('item' => $item, 'mode' => 'edit'), 'Edit licensee', 'licensees');
	}

	public function delete($id)
	{
		$this->require_perm('licensees.delete');
		if ($this->licensee_model->soft_delete($id))
		{
			audit_log('delete', 'licensee', $id);
			$this->session->set_flashdata('ok', 'Licensee removed.');
		}
		redirect('licensees');
	}

	private function _payload()
	{
		return array(
			'name'          => trim($this->input->post('name')),
			'contact_email' => trim($this->input->post('contact_email')),
			'organization'  => trim($this->input->post('organization')),
			'notes'         => trim($this->input->post('notes')),
		);
	}
}

/* End of file licensees.php */
