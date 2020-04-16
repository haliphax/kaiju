<?php if(! defined('BASEPATH')) exit();

class kujiin_kyo extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}

	# use skill
	function fire(&$actor)
	{
		if(! $this->ci->actor->hasSkill('kujiin_kyo', $actor['actor']))
			return false;
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		$this->ci->kujikiri->kujikiri_remove($actor['actor']);
		$msg = array(
			"You fold your hands into form, providing additional accuracy.");
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->addEffect('kujiin_kyo', $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;	
	}
}
