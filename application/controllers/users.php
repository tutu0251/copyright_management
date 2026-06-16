<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends Secure_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model(array('user_model', 'role_model'));
		$this->load->library('form_validation');
	}

	public function index()
	{
		$this->require_perm('users.manage');
		$this->render('users/index', array(
			'items' => $this->user_model->list_with_roles(),
			'roles' => $this->role_model->all(100, 'name ASC'),
			'error' => $this->session->flashdata('form_error'),
		), 'Users', 'users');
	}

	public function create()
	{
		$this->require_perm('users.manage');
		$this->form_validation->set_rules('display_name', 'Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
		$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');

		if ($this->form_validation->run() === FALSE)
		{
			$this->session->set_flashdata('form_error', validation_errors('', ' '));
			redirect('users');
			return;
		}

		$email = strtolower(trim($this->input->post('email')));
		if ($this->user_model->get_by_email($email))
		{
			$this->session->set_flashdata('form_error', 'Email already exists.');
			redirect('users');
			return;
		}

		$role = $this->role_model->get_by_slug($this->input->post('role') ? $this->input->post('role') : 'viewer');
		if ( ! $role)
		{
			$this->session->set_flashdata('form_error', 'Invalid role.');
			redirect('users');
			return;
		}

		$hash = password_hash((string) $this->input->post('password'), PASSWORD_BCRYPT);
		$uid = $this->user_model->create($email, $hash, trim($this->input->post('display_name')));
		$this->user_model->assign_role($uid, $role['id']);
		audit_log('create', 'user', $uid, array('email' => $email));
		$this->session->set_flashdata('ok', 'User created.');
		redirect('users');
	}

	public function toggle($id)
	{
		$this->require_perm('users.manage');
		$user = $this->user_model->get($id);
		if ( ! $user) show_404();
		$active = ((int) $user['is_active'] === 1) ? 0 : 1;
		$this->user_model->set_active($id, $active);
		audit_log($active ? 'activate' : 'deactivate', 'user', $id);
		$this->session->set_flashdata('ok', 'User updated.');
		redirect('users');
	}
}

/* End of file users.php */
