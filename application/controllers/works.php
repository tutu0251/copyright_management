<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Works extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('work_model');
		$this->load->model('asset_model');
	}

	public function index()
	{
		$this->require_perm('works.view');
		$this->render('works/index', array(
			'items' => $this->work_model->list_with_asset_stats(),
		), 'Works', 'works');
	}

	public function show($id)
	{
		$this->require_perm('works.view');
		$work = $this->work_model->get($id);
		if ( ! $work) show_404();
		$this->render('works/show', array(
			'work'   => $work,
			'assets' => $this->asset_model->for_work($id),
		), $work['title'], 'works');
	}

	public function create()
	{
		$this->require_perm('works.create');
		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$data = $this->_payload();
			$data['created_by'] = $this->current_user['id'];
			$id = $this->work_model->insert($data);
			audit_log('create', 'work', $id);
			$this->session->set_flashdata('ok', 'Work registered.');
			redirect('works/show/'.$id);
			return;
		}
		$this->render('works/form', array('item' => NULL, 'mode' => 'create'), 'New work', 'works');
	}

	public function edit($id)
	{
		$this->require_perm('works.update');
		$item = $this->work_model->get($id);
		if ( ! $item) show_404();

		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$this->work_model->update($id, $this->_payload());
			audit_log('update', 'work', $id);
			$this->session->set_flashdata('ok', 'Work updated.');
			redirect('works/show/'.$id);
			return;
		}
		$this->render('works/form', array('item' => $item, 'mode' => 'edit'), 'Edit work', 'works');
	}

	public function delete($id)
	{
		$this->require_perm('works.delete');
		if ($this->work_model->soft_delete($id))
		{
			audit_log('delete', 'work', $id);
			$this->session->set_flashdata('ok', 'Work removed.');
		}
		redirect('works');
	}

	private function _payload()
	{
		$registered = trim($this->input->post('registered_at'));
		return array(
			'title'            => trim($this->input->post('title')),
			'work_type'        => $this->input->post('work_type') ? $this->input->post('work_type') : 'Text',
			'creator'          => trim($this->input->post('creator')),
			'owner'            => trim($this->input->post('owner')),
			'copyright_status' => $this->input->post('copyright_status') ? $this->input->post('copyright_status') : 'draft',
			'risk_level'       => $this->input->post('risk_level') ? $this->input->post('risk_level') : 'Low',
			'description'      => trim($this->input->post('description')),
			'registered_at'    => $registered !== '' ? $registered : NULL,
		);
	}
}

/* End of file works.php */
