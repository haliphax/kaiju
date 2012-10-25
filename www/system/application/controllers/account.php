<?php if(! defined('BASEPATH')) exit();

class Account extends Controller
{
	# constructor
	function Account()
	{
		parent::Controller();
		
		if(file_exists($this->config->item('maintfile')))
		{
			header('Location: ' . site_url('login'));
			die();
		}
		
		$this->load->library('session');

		if($this->session->userdata('fb_user'))
		{
			header('Location: ' . site_url('game'));
			exit();
		}

		$this->load->model('user');
	}

	# default
	function index()
	{
		if($this->session->userdata('user') === false)
		{
			header('Location: ' . site_url('login'));
			return;
		}
		
		$this->load->view('account', array(
			'user' => $this->user->getInfo($this->session->userdata('user'))));
	}
	
	# update details ===========================================================
	function details()
	{
		$data = array('user' =>
			$this->user->getInfo($this->session->userdata('user')));
		$email = trim($this->input->post('email'));
		$pass = $this->input->post('newpass');
		$pass2 = $this->input->post('confirm');
		
		if($email != $data['user']['email'])
		{
			if($email == '' ||
				! preg_match('#[-_.!~a-z0-9]+@([-a-z0-9]+\.)+[a-z]{2,7}#i',
					$email))
			{
				$data['err'] .= 'E-mail address is invalid.&nbsp;';
			}
			else
			{
				if(! $this->user->setEmail($email,
					$this->session->userdata('user')))
				{
					$data['err'] .= 'E-mail address is already in use.&nbsp;';
				}
				else
					$data['msg'] .= 'E-mail address updated.&nbsp;';
			}
		}
		
		if($pass != '')
		{
			if(strlen($pass) < 6)
				$data['err'] .= 'Password must be at least 6 characters.&nbsp;';
			else if(strcmp($pass, $pass2) != 0)
				$data['err'] .= 'Passwords do not match.&nbsp;';
			else
			{
				if(! $this->user->setPassword(
					$this->session->userdata('user'),
					$pass))
				{
					$data['err'] .= 'Error changing password.&nbsp;';
				}
				else
					$data['msg'] .= 'Password changed.&nbsp;';
			}
		}
		
		if($data['err'] != '' || $data['msg'] != '')
			$data['user'] =
				$this->user->getInfo($this->session->userdata('user'));
		$this->load->view('account', $data);
	}
}
