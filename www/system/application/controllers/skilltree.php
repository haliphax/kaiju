<?php if(! defined('BASEPATH')) exit();

# skill tree ===================================================================

class skilltree extends Controller
{
	private $who;
	
	function skilltree()
	{
		parent::Controller();
		$this->load->library('session');
		
		if(! $this->session->userdata('actor'))
		{
			header('Location: ' . site_url('login'));
			die();
		}
		
		$this->load->model('actor');
		$this->load->model('skills');
		$this->who = $this->actor->getInfo($this->session->userdata('actor'));
	}
	
	function index()
	{
		$classes = $this->actor->getClasses($this->who['actor']);
		
		foreach($classes as $k => $c)
		{
			$classes[$k]['skills'] =
				$this->skills->getTree($c['aclass'], $this->who['actor']);
		}
		
		$data = array(
			'classes' => $classes,
			'who' => $this->who
			);
		$this->load->view('skilltree', $data);
	}
	
	# describe a skill =========================================================	
	function describe($skill)
	{
		echo json_encode($this->skills->getInfo($skill));
	}
	
	# purchase a skill =========================================================
	function purchase($abbrev, $aclass, $skill)
	{
		header("Location: " . site_url("skilltree/#class-{$abbrev}"));
		$this->skills->purchase($aclass, $skill, $this->who);
	}
}