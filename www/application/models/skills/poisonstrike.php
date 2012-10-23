<?php if(! defined('BASEPATH')) exit();

class poisonstrike extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}
	
	# poison strike ============================================================	
	function fire(&$actor, &$args)
	{
		$victim = $this->ci->actor->getInfo($args[0]);
		if(! $this->show($actor, $victim)) return;
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		$this->ci->actor->addEffect('poisonstrike', &$actor);
		$ret = $this->ci->actor->attackWith(&$victim, $weps[0], false,
			max(5, $this->ci->actor->getChanceToHit(&$actor, &$victim) - 6),
			0, &$actor, $fail);
		if(! $fail) $msg[] = "You attempt to poison {$victim['aname']}...";
		foreach($ret as $r) $msg[] = $r;
		$this->ci->actor->removeEffect('poisonstrike', &$actor);
		
		if(! $fail)
		{
			$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
			foreach($ret as $r) $msg[] = $r;
			$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], &$actor);
			foreach($ret as $r) $msg[] = $r;
		}
		
		return $msg;
	}
	
	
	function show(&$actor, &$victim)
	{
		if($victim['actor'] <= 0) return false;
		
		if(! $this->ci->skills->canMelee($victim['actor'], $actor['actor'],
			'melee'))
		{
			return false;
		}
		
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		if($weps[0]['distance'] == 'ranged' || $weps[0]['iname'] == 'fists')
			return false;
		if($victim['stat_hp'] <= 0)
			return false;
		return true;
	}
}