<?php if(! defined('BASEPATH')) exit();

class burningtouch extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->cost = $this->ci->skills->getCost('burningtouch');
	}

	# use skill
	function fire(&$actor, $args)
	{
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		$victim = $this->ci->actor->getInfo($args[0]);
		$wep = array(
			'iname' => 'fists',
			'dmg_min' => 5,
			'dmg_max' => 8,
			'distance' => 'melee',
			'dmg_type' => 'fire'
			);
		$msg = array();
		$ret = $this->ci->actor->attackWith($victim, $wep, false, false, false,
			$actor, &$fail);
		foreach($ret as $r) $msg[] = $r;
		if($fail) return $msg;
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function show(&$actor, &$victim)
	{
		if($this->ci->skills->canMelee($victim['actor'], $actor['actor'],
			'melee'))
		{
			return true;
		}
		
		return false;
	}
}
