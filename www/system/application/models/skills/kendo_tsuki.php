<?php if(! defined('BASEPATH')) exit();

class kendo_tsuki extends Model
{
	private $ci;
	private $cost;
	
	# constructor
	function kendo_tsuki()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('kendo_tsuki');
	}

	# use skill
	function fire(&$victim, &$actor)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->ci->skills->noap;
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		$chance = $this->ci->actor->getChanceToHit($actor, $victim);
		$chance -= 5;
		if($chance <= 0) $chance = 1;
		$ret = $this->ci->actor->attackWith(&$victim, $weps[0], 'head', $chance,
			false, &$actor, $fail, $hit);
		foreach($ret as $r) $msg[] = $r;
			
		if($hit['hit'])
		{
			$victim = $this->ci->actor->getInfo($victim['actor']);
			
			if($victim['stat_hp'] > 0)
			{
				$ret = $this->ci->actor->addEffect('bleeding', &$victim);
				foreach($ret as $r)
					$this->ci->actor->sendEvent($r, $victim['actor']);
				$msg[] = "Blood begins pouring from their wound.";
			}
		}
		
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('kendo', $actor['actor']);
	}
}
