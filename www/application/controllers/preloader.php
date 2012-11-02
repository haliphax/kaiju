<?php if(! defined('BASEPATH')) exit();

session_start();

class Preloader extends CI_Controller
{
	function Preloader()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->database();
	}

	function index()
	{
		if($this->session->userdata('preload') === false)
		{
			$imgs = array(
				'ui-spritesheet.png',
				'tiles-spritesheet.png',
				'walls-spritesheet.png',
				'pawns/person.png',
				'pawns/other.png'
			);

			$this->session->set_userdata('preload', true);
			$this->load->view('preloader',
				array('imgs' => json_encode($imgs))
			);
		}
		else
			$this->output->set_header('Location: ' . site_url('game'));
	}
}
