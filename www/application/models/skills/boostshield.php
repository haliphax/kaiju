<?php if(! defined('BASEPATH')) exit();

class boostshield extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->ci->load->model('clan');
	}

	function fire(&$actor)
	{
		if(! $this->show($actor)) return;
		$msg = array();
		$v = $this->ci->clan->getStrongholdShield($actor['clan']);
		
		if($this->ci->clan->incStrongholdShield($actor['clan'], 5))
		{
			$msg[] =
				"Focusing your energy outward, you increase the shield's power.";
			if($v + 5 >= 100)
				$msg[] = "The shield cannot be boosted any further.";
			$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
			foreach($ret as $r) $msg[] = $r;
			$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
			foreach($ret as $r) $msg[] = $r;
		}
		
		return $msg;
	}
	
	# show skill?
	function show(&$actor)
	{
		if($actor['indoors']) return false;
		
		if($actor['stat_mp'] < $this->cost['cost_mp']
			|| $actor['stat_ap'] < $this->cost['cost_ap'])
		{
			return false;
		}
		
		$this->ci->load->model('map');
		$cellinfo = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		if(! $cellinfo['clan']) return false;
		if($cellinfo['clan'] !== $actor['clan']) return false;
		if($this->ci->clan->getStrongholdShield($actor['clan']) >= 100)
			return false;
		
		if($actor['stat_ap'] < $this->cost['cost_ap']
			|| $actor['stat_mp'] < $this->cost['cost_mp'])
		{
			return false;
		}
		
		return true;
	}
}