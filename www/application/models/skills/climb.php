<?php if(! defined('BASEPATH')) exit();

class climb extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}

	# use skill
	function fire(&$actor)
	{
		if($actor['indoors'])
			return array("It's too cramped in here. Try going outdoors.");
		$this->ci->load->model('map');
		
		if(! $this->ci->map->cellHasClass('climb', $actor['map'], $actor['x'],
			$actor['y'], 0))
		{
			return array("There is nothing to climb here.");
		}
		
		$msg = array();
		
		if($this->ci->actor->hasEffect('climbing', $actor['actor']))
		{
			$ret = $this->ci->actor->removeEffect('climbing', $actor);
			foreach($ret as $r) $msg[] = $r;
		}
		else
		{
			$ret = $this->ci->actor->addEffect('climbing', $actor);
			foreach($ret as $r) $msg[] = $r;
		}
		
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function show(&$actor)
	{		
		$this->ci->load->model('map');
		
		if($this->ci->map->cellHasClass('climb', $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors']))
		{			
			return true;
		}
		
		return false;
	}
}
