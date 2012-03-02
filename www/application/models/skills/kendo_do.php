<?php if(! defined('BASEPATH')) exit();

class kendo_do extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function kendo_do()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('kendo_do');
	}

	# use skill
	function fire(&$victim, &$actor, $args)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->ci->skills->noap;
		$msg[] = "You attempt a well-placed body blow.";
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		$weps[0]['dmg_min'] = round($weps[0]['dmg_min'] * 1.25);
		$weps[0]['dmg_max'] = round($weps[0]['dmg_max'] * 1.25);
		$chance = $this->ci->actor->getChanceToHit($actor, $victim);
		$chance -= 3;
		if($chance <= 0) $chance = 1;
		$res = $this->ci->actor->attackWith(&$victim, $weps[0], 'torso',
			$chance, false, &$actor, $fail, $hit);
		foreach($res as $r) $msg[] = $r;
		
		if($hit['hit']
			&& $this->ci->actor->hasEffect('climbing', $victim['actor']))
		{
			$res = $this->ci->actor->removeEffect('climbing', &$victim);
			foreach($res as $r)
				$this->ci->actor->sendEvent($r, $victim['actor']);
			$msg[] = "You have knocked them to the ground!";
		}
		
		$res = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($res as $r) $msg[] = $r;
		return $msg;
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('kendo', $actor['actor']);
	}
}
