<?php if(! defined('BASEPATH')) exit();

class kujiin_rin extends NoCacheModel
{
	private $ci;
	private $cost;
	
	# constructor
	function kujiin_rin()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('kujiin_rin');
	}

	# use skill
	function fire(&$actor)
	{
		if(! $this->ci->actor->hasSkill('kujiin_rin', $actor['actor']))
			return false;
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		$this->ci->load->model('skills/kujikiri');
		$this->ci->kujikiri->kujikiri_remove($actor['actor']);
		$msg = array(
			"You fold your hands into form, providing additional strength.");
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->addEffect('kujiin_rin', &$actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
}
