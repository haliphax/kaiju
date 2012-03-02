<?php if(! defined('BASEPATH')) exit();

class hide extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function hide()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('hide');
	}
	
	# use the skill
	function fire(&$actor)
	{
		if(! $this->show($actor)) return false;
		$msg = array();
		$roll = rand(1, 20);
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], &$actor);
		foreach($ret as $r) $msg[] = $r;		
		
		if($roll > 3)
			$msg[] = "You look for someplace to hide, but can't find any.";
		else
		{
			$ret = $this->ci->actor->addEffect('hiding', &$actor);
			foreach($ret as $r) $msg[] = $r;
		}
		
		return $msg;
	}
	
	# show the skill?
	function show(&$actor)
	{
		if(! $actor['indoors']
			|| $this->ci->actor->hasEffect('hiding', $actor['actor']))
		{
			return false;
		}
		
		return true;
	}
}