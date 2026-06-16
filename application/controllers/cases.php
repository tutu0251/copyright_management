<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cases extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('case_model');
		$this->load->model('work_model');
	}

	public function index()
	{
		$this->require_perm('cases.view');
		$this->render('cases/index', array('items' => $this->case_model->list_decorated()), 'Cases', 'cases');
	}

	public function show($id)
	{
		$this->require_perm('cases.view');
		$item = $this->case_model->get_decorated($id);
		if ( ! $item) show_404();
		$this->render('cases/show', array(
			'item'  => $item,
			'notes' => $this->case_model->notes($id),
		), $item['title'], 'cases');
	}

	public function create()
	{
		$this->require_perm('cases.create');
		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$id = $this->case_model->insert($this->_payload());
			audit_log('create', 'case', $id);
			$this->session->set_flashdata('ok', 'Case opened.');
			redirect('cases/show/'.$id);
			return;
		}
		$this->_form(NULL, 'create');
	}

	public function edit($id)
	{
		$this->require_perm('cases.update');
		$item = $this->case_model->get($id);
		if ( ! $item) show_404();

		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$this->case_model->update($id, $this->_payload());
			audit_log('update', 'case', $id);
			$this->session->set_flashdata('ok', 'Case updated.');
			redirect('cases/show/'.$id);
			return;
		}
		$this->_form($item, 'edit');
	}

	public function status($id)
	{
		$this->require_perm('cases.status_update');
		$item = $this->case_model->get($id);
		if ( ! $item) show_404();
		$status = $this->input->post('case_status');
		if ($status)
		{
			$this->case_model->set_status($id, $status);
			audit_log('status_update', 'case', $id, array('case_status' => $status));
			$this->session->set_flashdata('ok', 'Case status changed.');
		}
		redirect('cases/show/'.$id);
	}

	public function note($id)
	{
		$this->require_perm('cases.update');
		$item = $this->case_model->get($id);
		if ( ! $item) show_404();
		$body = trim($this->input->post('body'));
		if ($body !== '')
		{
			$this->case_model->add_note($id, $this->current_user['id'], $body);
			audit_log('note_added', 'case', $id);
			$this->session->set_flashdata('ok', 'Note added.');
		}
		redirect('cases/show/'.$id);
	}

	public function delete($id)
	{
		$this->require_perm('cases.delete');
		if ($this->case_model->soft_delete($id))
		{
			audit_log('delete', 'case', $id);
			$this->session->set_flashdata('ok', 'Case removed.');
		}
		redirect('cases');
	}

	private function _form($item, $mode)
	{
		$this->render('cases/form', array(
			'item'  => $item,
			'mode'  => $mode,
			'works' => $this->work_model->options(),
		), $mode === 'edit' ? 'Edit case' : 'New case', 'cases');
	}

	private function _payload()
	{
		return array(
			'title'       => trim($this->input->post('title')),
			'work_id'     => $this->input->post('work_id') ? (int) $this->input->post('work_id') : NULL,
			'case_status' => $this->input->post('case_status') ? $this->input->post('case_status') : 'open',
			'priority'    => $this->input->post('priority') ? $this->input->post('priority') : 'medium',
			'description' => trim($this->input->post('description')),
		);
	}
}

/* End of file cases.php */
