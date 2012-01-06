<?php if(! defined('BASEPATH')) exit();

session_start();

class Preloader extends Controller
{
	function Preloader()
	{
		parent::Controller();
		$this->load->library('session');
	}

	function index()
	{
		if($this->session->userdata('preload') === false)
		{
			$this->session->set_userdata('preload', true);
			$this->load->view('preloader', array(
				'imgs' => json_encode(array(
					'ui/move-nw.gif',
					'ui/move-n.gif',
					'ui/move-ne.gif',
					'ui/move-w.gif',
					'ui/move-e.gif',
					'ui/move-sw.gif',
					'ui/move-s.gif',
					'ui/move-se.gif',
					'ui/door-icon.gif',
					'pawns/person.png',
					'pawns/other.png'
					))
				));
		}
		else
			header('Location: ' . site_url('game'));
	}
}
