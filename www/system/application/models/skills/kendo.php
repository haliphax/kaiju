<?php if(! defined('BASEPATH')) exit();

class kendo extends Model
{
	private $ci;
	
	# constructor
	function kendo()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
	}

	# use skill
	function fire(&$actor, $args)
	{
		$st = $args[1];
		if(! $this->ci->actor->hasSkill("kendo_{$st}", $actor['actor']))
			return false;
		$victim = $this->ci->actor->getInfo($args[0]);	
		if(! $this->show($actor, $victim)) return;
		$which = "kendo_{$st}";
		$this->ci->load->model('skills/' . $which);
		$ret = call_user_func(array($this->ci->$which, 'fire'),
			&$victim, &$actor);
		$msg = array();
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	# skill parameters
	function params(&$actor)
	{
		$s = <<<SQL
			select right(abbrev, char_length(abbrev) - 6) as abbrev,
				sname, cost_ap from actor_skill ak
			join skill s on ak.skill = s.skill where
			actor = ? and abbrev like 'kendo_%'
SQL;
		$q = $this->db->query($s, array($actor['actor']));
		$r = $q->result_array();
		$ret = array();
		foreach($r as $row)
			$ret[] = array($row['abbrev'],
				"{$row['sname']} ({$row['cost_ap']})");
		return $ret;	
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
