<?php if(! defined('BASEPATH')) exit();

class kata_mantis extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}

	# use skill
	function fire(&$actor)
	{
		if(! $this->ci->actor->hasSkill('kata_mantis', $actor['actor']))
			return false;
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		$this->ci->kata->kata_remove($actor['actor']);
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->addEffect('kata_mantis', $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('kata', $actor['actor']);
	}
}
