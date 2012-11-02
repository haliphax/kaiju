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
				'ui/move-nw.gif',
				'ui/move-n.gif',
				'ui/move-ne.gif',
				'ui/move-w.gif',
				'ui/move-e.gif',
				'ui/move-sw.gif',
				'ui/move-s.gif',
				'ui/move-se.gif',
				'ui/door-icon.gif',
				'walls/e.png',
				'walls/n.png',
				'walls/ne.png',
				'walls/ns.png',
				'walls/nse.png',
				'walls/nsw.png',
				'walls/nswe.png',
				'walls/nw.png',
				'walls/nwe.png',
				'walls/s.png',
				'walls/se.png',
				'walls/sw.png',
				'walls/swe.png',
				'walls/w.png',
				'walls/we.png',
				'pawns/person.png',
				'pawns/other.png'
			);
			$q = $this->db->query('select img from tile');
			$r = $q->result_array();
			
			foreach($r as $row)
			{
				$imgs[] = "tiles/{$row['img']}";
			}

			$this->session->set_userdata('preload', true);
			$this->load->view('preloader',
				array('imgs' => json_encode($imgs))
			);
		}
		else
			$this->output->set_header('Location: ' . site_url('game'));
	}
}
