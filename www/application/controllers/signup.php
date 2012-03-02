<?php if(! defined('BASEPATH')) exit();

class Signup extends CI_Controller
{
	function Signup()
	{
		parent::__construct();

		if(file_exists($this->config->item('maintfile')))
		{
			header('Location: ' . site_url('login'));
			die();
		}
	}

	function index()
	{
		$this->load->view('signup');
	}

	function submit()
	{
		$username = $this->input->post('username');
		$email = $this->input->post('email');
		$password = $this->input->post('pass');
		$confirm = $this->input->post('confirm');
		$err = '';

		if(trim($username) == '' || trim($email) == '' || trim($password) == '' || trim($confirm) == '')
			$err .= '<li>All fields are required</li>';
		else
		{
			if(! preg_match('#^[-_a-z0-9]+$#i', $username))
				$err .= '<li>Only -, _, a-z, 0-9 are allowed in usernames</li>';
			if(strlen($username) > 24)
				$err .= '<li>Usernames must be 24 characters or less</li>';
			$this->load->database();
			$q = $this->db->query('select 1 from user where uname = ?', $username);
			if($q->num_rows() > 0)
				$err .= '<li>Username is already taken</li>';
			if(! preg_match('#^[-_.a-z0-9]+@([-_a-z0-9]+[.])+[a-z]{2,7}$#i', $email))
				$err .= '<li>Invalid email address</li>';
			if(strlen($email) > 64)
				$err .= '<li>Email addresses must not exceed 64 characters in length</li>';
			$q = $this->db->query('select 1 from user where email = ?', $username);
			if($q->num_rows() > 0)
				$err .= '<li>Email address is already in use</li>';
			if(strlen($password) < 6 || strlen($password) > 24)
				$err .= '<li>Passwords must be between 6 and 24 characters in length</li>';
			if($password !== $confirm)
				$err .= '<li>Passwords do not match</li>';
		}

		if(strlen($err) > 0)
		{
			$err = "There were errors processing your submission:<ul>{$err}</ul>";
			$this->load->view('signup', array('err' => $err));
			return;
		}
		else
		{
			$this->load->library('session');
			$this->load->model('user');
			$id = $this->user->create($username, $password, $email);

			if(! $id)
			{
				$this->load->view('signup', array('err' => 'There were errors processing your submission:<ul><li>Error creating database record</li></ul>'));
				return;
			}

			$this->user->setLast($uid);
			$this->session->set_userdata('user', $id);
			header('Location: ' . site_url('characters'));			
		}
	}
}
