<?php if(! defined('BASEPATH')) exit();

class healingtouch extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}

	# use skill
	function fire(&$actor, $args)
	{
		if(! $args[0]) $args[0] = $actor['actor'];
		$victim = $this->ci->actor->getInfo($args[0]);
		if($victim['stat_hp'] <= 0)
			return array('They are dead. Bandages cannot help them now.');
		if($victim['stat_hp'] >= $victim['stat_hpmax'])
			return array('No healing is necessary.');
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		
		if(! $this->ci->skills->canMelee($victim['actor'], $actor['actor'],
			'melee'))
		{
			return array("You cannot reach them from here.");
		}
		
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$heal['hp'] = 5;
		$down = $victim['stat_hpmax'] - $victim['stat_hp'];
		if($down < $heal['hp']) $heal['hp'] = $down;
		$ret = $this->ci->actor->heal($actor, $victim, &$heal);
		foreach($ret as $r) $msg[] = $r;
		
		# self
		if($actor['actor'] == $victim['actor'])
		{
			$msg[] = 
				"Using mystical energy, you heal yourself for {$heal['hp']}HP.";
			return $msg;
		}
		
		$msg[] =
			"You lay your hands on {$victim['aname']}, healing them for "
			. "{$heal['hp']}HP.";
		$this->ci->actor->sendEvent(
			"{$actor['aname']} laid their hands on you, healing you for "
			. "{$heal['hp']}HP.", $victim['actor']);
		return $msg;
	}
	
	
	function show(&$actor, &$victim)
	{
		if($victim['actor'] <= 0) return false;
		
		if($this->ci->skills->canMelee($victim['actor'], $actor['actor'],
			'melee'))
		{
			return true;
		}
		
		return false;
	}
}
