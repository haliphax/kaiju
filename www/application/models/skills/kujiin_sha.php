<?php if(! defined('BASEPATH')) exit();

class kujiin_sha extends NoCacheModel
{
	private $ci;
	private $cost;
	
	# constructor
	function kujiin_sha()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('kujiin_sha');
	}

	# use skill
	function fire(&$actor)
	{
		if(! $this->ci->actor->hasSkill('kujiin_sha', $actor['actor']))
			return false;
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		$this->ci->load->model('skills/kujikiri');
		$this->ci->kujikiri->kujikiri_remove($actor['actor']);
		$msg = array(
			"You fold your hands into form, potentially healing yourself through combat."
			);
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->addEffect('kujiin_sha', &$actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;	
	}
}
