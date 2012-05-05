<?php if(! defined('BASEPATH')) exit();

class caltrops extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('caltrops');
	}

	# use skill
	function fire(&$actor)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->ci->skills->noap;
		$s = <<<SQL
			select actor from actor
			where map = ? and x = ? and y = ? and actor != ?
				and stat_hp > 0
			and actor not in
				(select actor from actor_class join class_actor
				where actor = ? and abbrev = 'ninja')
			limit 3
SQL;
		$q = $this->db->query($s, array($actor['map'], $actor['x'],
			$actor['y'], $actor['actor'], $actor['actor']));
		$r = $q->result_array();
		foreach($r as $row)
			$this->ci->actor->addEffect('caltrops', &$row);
		$msg = array("You litter the floor with caltrops.");
		$res = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($res as $r) $msg[] = $r;
		return $msg;
	}
	
	function show()
	{
		return true;
	}
}
