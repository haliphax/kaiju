<?php if(! defined('BASEPATH')) exit();

class poisonweapon extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->cost = $this->ci->skills->getCost('poisonweapon');
	}

	# use skill
	function fire(&$actor)
	{
		if($this->ci->actor->hasEffect('poisonweapon', $actor['actor']))
			return array("Your weapon is already coated in poison.");
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->ci->skills->noap;
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		foreach($weps as $w)
			if($w['iname'] == 'fists')
				return array("You do not have a weapon to coat.");
		$msg = array();
		$res = $this->ci->actor->addEffect('poisonweapon', &$actor);
		foreach($res as $r) $msg[] = $r;
		$res = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($res as $r) $msg[] = $r;
		return $msg;
	}
	
	# spent AP event
	function ap($ap, &$actor)
	{
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		foreach($weps as $w)
			if($w['iname'] == 'fists')
				$this->ci->actor->removeEffect('poisonweapon', &$actor);
	}

	# show skill?
	function show(&$actor)
	{
		if($this->ci->actor->hasEffect('poisonweapon', $actor['actor']))
			return false;
		return true;
	}
}
