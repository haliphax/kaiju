<?php if(! defined('BASEPATH')) exit();

class Login extends CI_Controller
{
	private $die = 0;
	
	function __construct()
	{
		parent::__construct();

		if($this->session->userdata('fb_user'))
		{
			$this->load->helper('url');
			redirect(site_url('game'), 'location');
			exit();
		}

		$this->output->set_header(
			"Cache-Control: no-store, no-cache, must-revalidate");
		$this->load->spark("cache/2.0.0");
		$this->news = $this->cache->get("twitter_feed", true);

		if(file_exists($this->config->item('maintfile')))
		{
			$this->load->view('login', array('maint' => 1, 'error' =>
				'The system is currently down for maintenance.',
				'news' => $this->news));
			$this->die = 1;
		}
		
		$this->load->model('user');
	}

	function index()
	{
		$this->session->sess_destroy();
		if($this->die == 1) return;
		$this->load->view('login', array('error' => '',
			'news' => $this->news));
	}

	function check()
	{
		if($this->die == 1) return;
		$user = trim(strtolower($this->input->post('user')));
		$pass = trim($this->input->post('pass'));
		$uid = $this->user->checkLogin($user, $pass);
		
		if(! $uid)
			$this->load->view('login', array(
				'error' => 'Failed login. <a href="' . site_url('login/forgot') . '">Forgot your password?</a>',
				'news' => $this->news));
		else
		{
			$this->user->setLast($uid);
			$this->session->set_userdata('user', $uid);
			$this->output->set_header('Location: ' . site_url('characters'));
		}
	}

	function reset($token)
	{
		$this->session->sess_destroy();
		$newpass = $this->input->post('password');
		$confirm = $this->input->post('confirm');
		
		if($newpass !== $confirm)
		{
			$this->load->view(
				'reset',
				array('err' => 'Passwords do not match.'));
			return;	
		}

		$this->load->model('user');
		$user = $this->user->checkResetToken($token);

		if(! $user)
		{
			$this->load->view(
				'reset',
				array('err' => 'No such token.'));
			return;
		}

		if(! $newpass)
		{
			$this->load->view('reset');
			return;
		}

		$this->user->setPassword($user, $newpass);
		$this->load->view(
			'reset',
			array('success' => 1));
	}

	function forgot()
	{
		$this->session->sess_destroy();
		$user = $this->input->post('username');
		$email = $this->input->post('email');

		if($user && $email)
		{
			$this->load->model('user');

			if(! $this->user->checkEmail($user, $email))
			{
				$this->load->view('forgot',
					array('err' => 'Your email address does not match our records.'));
				return;
			}

			$token = $this->user->getResetToken($user);
			$link = site_url('login/reset/' . $token);
			$this->load->helper('email');
			$txt = <<<TXT
Visit the link below to reset your kaiju! account password.

{$link}
TXT;
			send_email($email, 'kaiju! password reset', $txt);
			$this->load->view('forgot', array('success' => 1));
			return;
		}

		$this->load->view('forgot');
	}
}
