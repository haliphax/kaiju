<?php if(! defined('BASEPATH')) exit();

class srch extends CI_Model
{
	private $ci;
	private $cost;
	
	function srch()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('action');
		$this->cost = $this->ci->action->getCost("global", "srch");
	}
	
	function fire(&$actor)
	{
		$this->ci->load->model('actor');
		$this->ci->load->model('map');
		if($this->ci->actor->getEncumbrance($actor['actor']) > 60)
			return array("You are too encumbered to move.");
		$msg = array();
		$ret = $this->ci->map->searchCell(&$actor, $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors']);
		
		if($ret[0] == false && $ret[1] == false)
			$msg[] = 'You search, but find nothing.';
		else
		{
			if($ret[0] !== false)
				$msg[] = "You search the surroundings and find a(n) {$ret[0]}.";
			if($ret[1] !== false)
				$msg[] = "You found {$ret[1]}! They were hiding.";
		}
		
		$ret = $this->ci->actor->spendAP($this->cost, &$actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function show()
	{
		return true;
	}
}