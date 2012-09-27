<?php if(! defined('BASEPATH')) exit();

class architect extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}

	# use skill
	function fire(&$actor, $args)
	{
		if($this->ci->actor->isOverEncumbered($actor['actor']))
			return array("You do not have room in your inventory.");
		$cr = $args[0];
		$r = $this->ci->skills->getInfo($cr);
		$which = $r['abbrev'];
		$cost = $this->ci->skills->getCost($which);
		if($cost['cost_ap'] > $actor['stat_ap'])
			return $this->ci->skills->noap;
		if($cost['cost_mp'] > $actor['stat_mp'])
			return $this->ci->skills->nomp;
		$this->ci->load->model("skills/$which");
		$ret = call_user_func(array($this->ci->$which, 'fire'), &$actor);
		$msg = array();
		$this->ci->actor->addXP($actor, 1);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function show()
	{
		return true;
	}
	
	function params(&$actor)
	{
		$s = <<<SQL
			select ak.skill, sname, cost_ap from actor_skill ak
			join skill s on ak.skill = s.skill where
			actor = ? and abbrev like 'architect_%'
SQL;
		$q = $this->db->query($s, $actor['actor']);
		$r = $q->result_array();
		foreach($r as $row)
			$ret[] = array($row['skill'],
				"{$row['sname']} ({$row['cost_ap']})");
		return $ret;
	}

	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('architect_bp_shack', $actor['actor']);
	}
}
