<?php if(! defined('BASEPATH')) exit();

class kendo_men extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}

	# use skill
	function fire(&$victim, &$actor)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->ci->skills->noap;
		$msg[] = "You summon your strength and strike at your opponent's head.";
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		$weps[0]['dmg_min'] = round($victim['stat_hpmax'] / 5);
		$weps[0]['dmg_max'] = $weps[0]['dmg_min'];
		$chance = $this->ci->actor->getChanceToHit($actor, $victim);
		$chance -= 3;
		if($chance <= 0) $chance = 1;
		$ret = $this->ci->actor->attackWith($victim, $weps[0], 'head', $chance,
			0, $actor, $fail);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('kendo', $actor['actor']);
	}
}
