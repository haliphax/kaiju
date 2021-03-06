<?php if(! defined('BASEPATH')) exit();

class Game extends CI_Controller
{
	# constructor
	function Game()
	{
		parent::__construct();
		#$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
		#$this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
		#$this->output->set_header("Pragma: no-cache");

		if(file_exists($this->config->item('maintfile')))
		{
			$this->session->unset_userdata('fb_user');
			$this->output->set_header('Location: ' . site_url('login'));
			die();
		}

		if($this->session->userdata('user') === false)
		{
			$this->output->set_header('Location: ' . site_url('login'));
			return;
		}
		else if($this->session->userdata('actor') === false)
		{
			$this->output->set_header('Location: ' . site_url('characters'));
			return;
		}

		$this->load->model('actor');
	}

	# default
	function index()
	{
		$this->load->model('actor');
		$char = $this->actor->getInfo($this->session->userdata('actor'));
		$this->load->view('game', array('aname' => $char['aname']));
		return;
	}
}
