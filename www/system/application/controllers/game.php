<?php if(! defined('BASEPATH')) exit();

class Game extends Controller
{
	# constructor
	function Game()
	{
		parent::Controller();
		#$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
		#$this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
		#$this->output->set_header("Pragma: no-cache"); 
		
		if(file_exists($this->config->item('maintfile'))
			|| ($_SERVER['SERVER_NAME'] == 'kaiju.oddnetwork.org' && $this->session->userdata('fb_user')))
		{
			$this->session->unset_userdata('fb_user');
			header('Location: ' . site_url('login'));
			die();
		}
		
		$this->load->library('session');

		if($this->session->userdata('user') === false)
		{
			header('Location: ' . site_url('login'));
			return;
		}
		else if($this->session->userdata('actor') === false)
		{
			header('Location: ' . site_url('characters'));
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
