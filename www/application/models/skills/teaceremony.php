<?php if(! defined('BASEPATH')) exit();

class teaceremony extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function teaceremony()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('teaceremony');
	}

	# use skill
	function fire(&$actor, $args)
	{
		if(! $args[0]) return;
		$msg = array();
		
		$victim = $this->ci->actor->getInfo($args[0]);
		if($victim['stat_hp'] <= 0)
			return array('They are dead. Tea cannot help them now.');
		if($victim['stat_hp'] >= $victim['stat_hpmax'])
			return array('No healing is necessary.');
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		
		if(! $this->ci->skills->canMelee($victim['actor'], $actor['actor'],
			'melee'))
		{
			return array("You cannot reach them from here.");
		}
		
		$ret = $this->ci->actor->addEffect('exertion', $actor);
		foreach($ret as $r) $msg[] = $r;
		$this->ci->load->model('pdata');
		$cnt = $this->ci->pdata->get('effect', 'exertion', $actor['actor']);
		
		if($cnt < 16)
		{
			$cnt += 5;
			$this->ci->pdata->set('effect', 'exertion', $cnt, $actor['actor']);
		}
		else		
			return array("You are far too fatigued for pouring tea.");
		
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$heal['hp'] = 2;
		$down = $victim['stat_hpmax'] - $victim['stat_hp'];
		if($down < $heal['hp']) $heal['hp'] = $down;
		$ret = $this->ci->actor->heal($actor, $victim, &$heal);
		foreach($ret as $r) $msg[] = $r;
		# self
		if($actor['actor'] == $victim['actor'])
			return;
		$msg[] =
			"You pour tea for {$victim['aname']}, healing them for "
			. "{$heal['hp']}HP.";
		$this->ci->actor->sendEvent(
			"{$actor['aname']} poured you some tea, healing you for "
			. "{$heal['hp']}HP.", $victim['actor']);
		return $msg;
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
