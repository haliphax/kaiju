<?php if(! defined('BASEPATH')) exit();

class Fb extends CI_Controller
{
	private $die = 0;

	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->model('facebook');

		if(file_exists($this->config->item('maintfile')))
		{
			$this->load->view('login', array('maint' => 1, 'error' =>
				'The system is currently down for maintenance.',
				'news' => $this->news));
			$this->die = 1;
		}
	}

	function index()
	{
		if($this->die == 1)
			return;
		$req = $this->input->post('signed_request');

		if($req)
		{
			list($sig, $payload) = explode('.', $req, 2);
			$data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);

			if($data)
			{
				if(empty($data['user_id']))
					echo '<script type="text/javascript">top.location.href="https://www.facebook.com/dialog/oauth?client_id=197452126983118&redirect_uri=' . urlencode(site_url('fb')) . '";</script>';
				else
				{
					$this->session->set_userdata('fb_user', $data['user_id']);
					$user = $this->facebook->getUser();

					if(! $user)
						$this->output->set_header('Location: ' . site_url('fb/create'));
					else
					{
						$this->session->set_userdata('user', $user);
						$this->output->set_header('Location: ' . site_url('game'));
					}
				}
			}
		}
		else
			$this->output->set_header('Location: ' . site_url('login'));
	}

	function create($confirm = 0)
	{
		if($this->die == 1)
			return;

		if($confirm == 1)
		{
			if($this->facebook->registerNewUser())
				$this->output->set_header('Location: ' .site_url('characters'));
			else
				$this->output->set_header('Location: ' .site_url('fb'));
			return;
		}

		$this->load->view('fbcreate');
	}

	# opt out of account creation - send to "oh, well. thanks anyway" page
	function nope()
	{
		echo "Fine, then. Be that way.";
	}

	# link existing kaiju! account with facebook account
	function link($post = 0)
	{
		if($this->die == 1)
			return;

		if($post == 0)
		{
			$this->load->view('fblink');
			return;
		}

		$this->load->model('user');
		$user = trim(strtolower($this->input->post('user')));
		$pass = trim($this->input->post('pass'));
		$uid = $this->user->checkLogin($user, $pass);

		if(! $uid)
		{
			$this->load->view('fblink', array('msg' => 'Invalid login'));
			return;
		}

		$this->facebook->linkUser($uid);
		$this->output->set_header('Location: ' . site_url('characters'));
	}
}
