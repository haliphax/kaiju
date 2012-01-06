<?php if(! defined('BASEPATH')) exit();

class aimedstrike extends Model
{
	private $ci;
	private $cost;
	
	# constructor
	function aimedstrike()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('aimedstrike');
	}

	# use skill
	function fire(&$actor, $args)
	{
		$weps = $this->ci->actor->getWeapons($actor['actor']);		
		foreach($weps as $w)
			if($w['distance'] == 'ranged')
				return array("You do not have a melee weapon equipped.");
		$victim = $this->ci->actor->getInfo($args[0]);
		
		if($victim['stat_hp'] <= 0)
			return array('You hit their unmoving, dead body. Great aim!');
		$t = strtolower($args[1]);
		$msg = array();
		
		foreach($weps as $w)
		{
			$ret = $this->ci->actor->attackWith(&$victim, $w, $t, 7, false,
				&$actor, $fail);
			if(! $fail) $msg[] = 'You carefully aim your attack...';
			foreach($ret as $r) $msg[] = $r;
			if($fail) break;
		}
		
		if(! $fail)
		{
			$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
			foreach($ret as $r) $msg[] = $r;
			$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], &$actor);
			foreach($ret as $r) $msg[] = $r;
		}
		
		return $msg;
	}
	
	# skill parameters
	function params(&$actor)
	{
		return $this->ci->skills->bodyparts;
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
