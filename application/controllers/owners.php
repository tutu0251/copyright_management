<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Owners extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('owner_model');
	}

	public function index()
	{
		$this->require_perm('owners.view');
		$this->render('owners/index', array('items' => $this->owner_model->all()), 'Owners', 'owners');
	}

	public function create()
	{
		$this->require_perm('owners.create');
		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$id = $this->owner_model->insert($this->_payload());
			audit_log('create', 'owner', $id);
			$this->session->set_flashdata('ok', 'Owner created.');
			redirect('owners');
			return;
		}
		$this->render('owners/form', array('item' => NULL, 'mode' => 'create'), 'New owner', 'owners');
	}

	public function edit($id)
	{
		$this->require_perm('owners.update');
		$item = $this->owner_model->get($id);
		if ( ! $item) show_404();

		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$this->owner_model->update($id, $this->_payload());
			audit_log('update', 'owner', $id);
			$this->session->set_flashdata('ok', 'Owner updated.');
			redirect('owners');
			return;
		}
		$this->render('owners/form', array('item' => $item, 'mode' => 'edit'), 'Edit owner', 'owners');
	}

	public function delete($id)
	{
		$this->require_perm('owners.delete');
		if ($this->owner_model->soft_delete($id))
		{
			audit_log('delete', 'owner', $id);
			$this->session->set_flashdata('ok', 'Owner removed.');
		}
		redirect('owners');
	}

	private function _payload()
	{
		return array(
			'legal_name'  => trim($this->input->post('legal_name')),
			'entity_type' => $this->input->post('entity_type') ? $this->input->post('entity_type') : 'individual',
			'email'       => trim($this->input->post('email')),
		);
	}
}

/* End of file owners.php */
