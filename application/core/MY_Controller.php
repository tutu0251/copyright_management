<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Base controllers for the Copyright Management app.
 *
 * CodeIgniter loads this file (MY_ subclass prefix) before any controller, so
 * the extra base classes below are available to controllers that extend them.
 *
 *   MY_Controller     — shared helpers, view rendering.
 *   Public_Controller — no auth (login / register).
 *   Secure_Controller — requires a logged-in user; loads RBAC permissions and
 *                       exposes require_perm(), mirroring the old Express
 *                       requireAuth / requirePermission middleware.
 */
class MY_Controller extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Render a view wrapped in the app chrome (sidebar + topbar).
	 * Public pages pass $chrome = FALSE for the bare auth layout.
	 */
	protected function render($view, $data = array(), $title = 'Copyright Management', $active = '', $chrome = TRUE)
	{
		$data = is_array($data) ? $data : array();
		if ($chrome)
		{
			$this->load->view('layouts/head', array('title' => $title, 'chrome' => TRUE));
			$this->load->view('layouts/sidebar', array(
				'current_user' => isset($this->current_user) ? $this->current_user : NULL,
				'active'       => $active,
			));
			$this->load->view($view, $data);
			$this->load->view('layouts/footer', array('chrome' => TRUE));
		}
		else
		{
			$this->load->view('layouts/head', array('title' => $title, 'chrome' => FALSE));
			$this->load->view($view, $data);
			$this->load->view('layouts/footer', array('chrome' => FALSE));
		}
	}
}

/**
 * Pages anyone can reach (login, register, logout).
 */
class Public_Controller extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
	}
}

/**
 * Authenticated area. Every controller behind the sign-in wall extends this.
 */
class Secure_Controller extends MY_Controller {

	/** @var array current user row (id, email, display_name, ...) */
	public $current_user = NULL;

	/** @var array list of permission slugs the user holds */
	public $perms = array();

	public function __construct()
	{
		parent::__construct();

		$this->load->model('user_model');
		$this->load->model('audit_model');

		$user_id = $this->session->userdata('user_id');
		if ( ! $user_id)
		{
			$this->_reject();
		}

		$user = $this->user_model->get($user_id);
		if ( ! $user OR (int) $user['is_active'] !== 1)
		{
			// Stale or deactivated session — clear and bounce to login.
			$this->session->sess_destroy();
			$this->_reject();
		}

		$this->current_user = $user;
		// Re-derive permissions every request (roles can change mid-session),
		// matching the original middleware behaviour.
		$this->perms = $this->user_model->permission_slugs($user_id);
	}

	private function _reject()
	{
		if ($this->input->is_ajax_request())
		{
			show_error('Authentication required', 401);
		}
		redirect('auth/login');
		exit;
	}

	/**
	 * Gate the current action by permission slug. Renders 403 and halts when the
	 * user lacks it. Mirrors requirePermission() in the old API.
	 */
	protected function require_perm($slug)
	{
		if ( ! in_array($slug, $this->perms, TRUE))
		{
			$this->output->set_status_header(403);
			$this->load->view('layouts/head', array('title' => 'Forbidden', 'chrome' => TRUE));
			$this->load->view('layouts/sidebar', array('current_user' => $this->current_user, 'active' => ''));
			$this->load->view('errors/403', array('permission' => $slug));
			$this->load->view('layouts/footer', array('chrome' => TRUE));
			exit;
		}
	}
}

/* End of file MY_Controller.php */
