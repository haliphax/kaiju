<?php if(! defined('BASEPATH')) exit();

class meditation extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}

	# use skill
	function fire(&$actor)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->ci->skills->noap;
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->addEffect('meditation', $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}

	# show skill?
	function show(&$actor)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap']) return false;
		return true;
	}
}
