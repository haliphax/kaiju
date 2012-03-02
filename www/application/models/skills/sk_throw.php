<?php if(! defined('BASEPATH')) exit();

class sk_throw extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function sk_throw()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('sk_throw');
	}

	# use skill
	function fire(&$actor, $args)
	{
		$victim = $this->ci->actor->getInfo($args[0]);
		$ins = $this->ci->actor->getInstanceOf($args[1], $actor['actor']);
		if(! $ins) return array("You don't have anything to throw.");
		
		if($victim['stat_hp'] <= 0)
			return array(
				"They're dead&mdash;they can't play catch with you anymore.");
		$msg = array();
		
		$this->ci->load->model('item');
		$wep = $this->ci->item->describe($args[1]);
		$wep['no_ammo'] = true;
		$cth = 11;
		
		# check for Thrown Weapons Mastery
		if($this->ci->actor->hasSkill('thrownwepsmaster'))
		{
			$cth++; # +5% CTH
			$wep['dmg'] = round($wep['dmg'] * 1.1); # +10% damage
		}
		
		$ret = $this->ci->actor->attackWith(&$victim, $wep, false, $cth, false,
			&$actor, $fail);
		foreach($ret as $r) $msg[] = $r;
		
		if(! $fail)
		{
			$this->ci->actor->dropItems(array($ins), $actor['actor']);
			$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
			foreach($ret as $r) $msg[] = $r;
			$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], &$actor);
			foreach($ret as $r) $msg[] = $r;
		}
		
		return $msg;	
	}
	
	function params(&$actor)
	{
		$weps = $this->ci->actor->getItems($actor['actor'], 'throwable');
		$ret = array();
		foreach($weps as $w)
			$ret[] = array($w['inum'], "{$w['iname']} [{$w['num']}]");
		return $ret;
	}
	
	function show(&$actor)
	{
		if(! $this->ci->actor->getItems($actor['actor'], 'throwable'))
			return false;
		return true;
	}
}
