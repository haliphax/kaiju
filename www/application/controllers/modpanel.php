<?php if(! defined('BASEPATH')) exit();

class modpanel extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->model('user');
		if(! $this->session->userdata('user'))
			die(header('Location: ' . site_url('login')));
		if(! $this->user->isMod($this->session->userdata('user')))
			die(header('Location: ' . site_url('game')));
	}
	
	function index()
	{
		$this->load->view('modpanel');
	}
}
