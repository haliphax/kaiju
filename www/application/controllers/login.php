<?php if(! defined('BASEPATH')) exit();

class Login extends CI_Controller
{
	private $die = 0;
	
	function __construct()
	{
		parent::__construct();

		if($this->session->userdata('fb_user'))
		{
			$this->output->set_header('Location: ' . site_url('game'));
			exit();
		}

		$this->output->set_header(
			"Cache-Control: no-store, no-cache, must-revalidate");
		$this->load->spark("cache/2.0.0");
		$this->news = $this->cache->get("twitter_feed", true);

		if($this->news == false)
		{
			$ch = curl_init($xml_url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$result = curl_exec($ch);
			curl_close($ch);
			$xml = simplexml_load_string($result);
			$this->news = "<ul id='tweets'>";
			$tweets = 0;

			foreach($xml as $status)
			{
				$this->news .= "<li>";
				$text = $status->text;

				# convert URLs into links
				$text = preg_replace(
					"#(https?://([-a-z0-9]+\.)+[a-z]{2,5}([/?][-a-z0-9!\#()/?&+]*)?)#i", "<a href='$1' target='_blank'>$1</a>",
					$text);
				# convert protocol-less URLs into links
				$text = preg_replace(
					"#(?!https?://|<a[^>]+>)(^|\s)(([-a-z0-9]+\.)+[a-z]{2,5}([/?][-a-z0-9!\#()/?&+.]*)?)\b#i", "$1<a href='http://$2'>$2</a>",
					$text);
				# convert @mentions into follow links
				$text = preg_replace(
					"#(?!https?://|<a[^>]+>)(^|\s)(@([_a-z0-9\-]+))#i", "$1<a href=\"{$instance['mention_url']}$3\" title=\"Follow $3\" target=\"_blank\">@$3</a>",
					$text);
				# convert #hashtags into tag search links
				$text = preg_replace(
					"#(?!https?://|<a[^>]+>)(^|\s)(\#([_a-z0-9\-]+))#i", "$1<a href='{$instance['hashtag_url']}$3' title='Search tag: $3' target='_blank'>#$3</a>",
					$text);	

				$this->news .= "{$text}<span>";
				$this->news .= date('D M j @ g:i A', strtotime($status->created_at) + (-5 * 60));
				$this->news .= "</span></li>";

				if(++$tweets == 5)
					break;
			}

			$this->news .= "</ul>";
			$this->cache->write($this->news, "twitter_feed", 900);
		}

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
