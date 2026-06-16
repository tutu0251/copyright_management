<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Roles extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('role_model', 'permission_model'));
	}

	public function index()
	{
		$this->require_perm('settings.manage');
		$this->render('roles/index', array('items' => $this->role_model->list_with_counts()), 'Roles & permissions', 'roles');
	}

	public function edit($id)
	{
		$this->require_perm('settings.manage');
		$role = $this->role_model->get($id);
		if ( ! $role) show_404();

		if (strtoupper($this->input->server('REQUEST_METHOD')) === 'POST')
		{
			$slugs = $this->input->post('permissions');
			if ( ! is_array($slugs)) $slugs = array();
			$this->role_model->set_permissions($id, $slugs);
			audit_log('update', 'role', $id, array('permission_count' => count($slugs)));
			$this->session->set_flashdata('ok', 'Permissions updated.');
			redirect('roles');
			return;
		}

		// Group permissions by their slug prefix (works.*, licenses.*, ...).
		$groups = array();
		foreach ($this->permission_model->all(200, 'slug ASC') as $p)
		{
			$parts = explode('.', $p['slug'], 2);
			$groups[$parts[0]][] = $p;
		}

		$this->render('roles/form', array(
			'role'    => $role,
			'groups'  => $groups,
			'granted' => $this->role_model->permission_slugs($id),
		), 'Edit role', 'roles');
	}
}

/* End of file roles.php */
