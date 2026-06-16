<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Session-based authentication (replaces the old JWT auth routes).
 *   login / register / logout.
 */
class Auth extends Public_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
		$this->load->model('role_model');
		$this->load->library('form_validation');
	}

	private function is_post()
	{
		return strtoupper($this->input->server('REQUEST_METHOD')) === 'POST';
	}

	public function index()
	{
		redirect('auth/login');
	}

	public function login()
	{
		if ($this->session->userdata('user_id'))
		{
			redirect('dashboard');
			return;
		}

		$data = array('error' => '', 'email' => '');

		if ($this->is_post())
		{
			$email = strtolower(trim($this->input->post('email')));
			$password = (string) $this->input->post('password');
			$data['email'] = $email;

			$user = $this->user_model->get_by_email($email);
			if ( ! $user OR ! password_verify($password, $user['password_hash']))
			{
				audit_log('login_failed', 'user', '', array('email' => $email), NULL);
				$data['error'] = 'Invalid credentials.';
			}
			elseif ((int) $user['is_active'] !== 1)
			{
				$data['error'] = 'Account inactive.';
			}
			else
			{
				$this->user_model->touch_login($user['id']);
				$this->session->set_userdata('user_id', (int) $user['id']);
				audit_log('login', 'user', $user['id'], array(), $user['id']);
				redirect('dashboard');
				return;
			}
		}

		$this->render('auth/login', $data, 'Sign in', '', FALSE);
	}

	public function register()
	{
		if ($this->session->userdata('user_id'))
		{
			redirect('dashboard');
			return;
		}

		$data = array('error' => '', 'ok' => '', 'name' => '', 'email' => '');

		if ($this->is_post())
		{
			$this->form_validation->set_rules('name', 'Name', 'required|trim');
			$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email');
			$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');

			$data['name'] = trim($this->input->post('name'));
			$data['email'] = strtolower(trim($this->input->post('email')));

			if ($this->form_validation->run() === FALSE)
			{
				$data['error'] = validation_errors('', ' ');
			}
			elseif ($this->user_model->get_by_email($data['email']))
			{
				$data['error'] = 'Email already exists.';
			}
			else
			{
				$viewer = $this->role_model->get_by_slug('viewer');
				if ( ! $viewer)
				{
					$data['error'] = 'Default viewer role not configured. Load sql/seed.sql.';
				}
				else
				{
					$hash = password_hash((string) $this->input->post('password'), PASSWORD_BCRYPT);
					$uid = $this->user_model->create($data['email'], $hash, $data['name']);
					$this->user_model->assign_role($uid, $viewer['id']);
					audit_log('register', 'user', $uid, array('email' => $data['email']), NULL);
					$this->session->set_flashdata('ok', 'Registration successful. Please sign in.');
					redirect('auth/login');
					return;
				}
			}
		}

		$this->render('auth/register', $data, 'Create account', '', FALSE);
	}

	public function logout()
	{
		$uid = $this->session->userdata('user_id');
		if ($uid)
		{
			audit_log('logout', 'user', $uid, array(), $uid);
		}
		$this->session->sess_destroy();
		redirect('auth/login');
	}
}

/* End of file auth.php */
