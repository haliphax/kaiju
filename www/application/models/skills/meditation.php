<?php if(! defined('BASEPATH')) exit();

class meditation extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function meditation()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('meditation');
	}

	# use skill
	function fire(&$actor)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->ci->skills->noap;
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->addEffect('meditation', &$actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}

	# show skill?
	function show(&$actor)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap']) return false;
		return true;
	}
}
