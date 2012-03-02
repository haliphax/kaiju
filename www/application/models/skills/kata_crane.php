<?php if(! defined('BASEPATH')) exit();

class kata_crane extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function kata_crane()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('kata_crane');
	}

	# use skill
	function fire(&$actor)
	{
		if(! $this->ci->actor->hasSkill('kata_crane', $actor['actor']))
			return false;
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		$this->ci->load->model('skills/kata');
		$this->ci->kata->kata_remove($actor['actor']);
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->addEffect('kata_crane', &$actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('kata', $actor['actor']);
	}
}
