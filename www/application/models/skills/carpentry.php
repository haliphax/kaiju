<?php if(! defined('BASEPATH')) exit();

class carpentry extends CI_Model
{
	private $ci;
	
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->ci->load->model('item');
	}

	function fire(&$actor, $args)
	{
		if($this->ci->actor->isOverEncumbered($actor['actor']))
			return array("You are too encumbered for such hard work.");
		$recipe = $args[0];
		$r = $this->ci->skills->getInfo($recipe);
		$which = $r['abbrev'];
		return $this->$which(&$actor);
	}
	
	function params(&$actor)
	{
		$skills = $this->ci->actor->getSkills($actor, -1, 'carpentry', true);
		$ret = array();
		foreach($skills as $s)
			$ret[] = array($s['skill'], "{$s['sname']} ({$s['cost_ap']}AP)");
		return $ret;
	}
	
	function show(&$actor)
	{
		$this->ci->load->model('map');
		return $this->ci->map->cellHasClass('workbench', $actor['map'],
			$actor['x'], $actor['y'], $actor['indoors'], $cellinfo);
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('r_planks', $actor['actor']);
		$this->ci->actor->addSkill('r_woodarrows', $actor['actor']);
	}
	
# recipes ======================================================================

	# wood planks ==============================================================
	function r_planks(&$actor)
	{
		$lmb = $this->ci->actor->getInstanceOf(
			$this->ci->item->getByName('lumber'), $actor['actor'], 1);
		if(! $lmb) return array("This recipe requires 1 Lumber.");
		$this->ci->actor->dropItems(array($lmb), $actor['actor']);
		$cnt = rand(1, 2);
		$a = 0;
		$i = $this->ci->item->getByName('wood plank');
		
		for($a = 0; $a < $cnt; $a++)
		{
			$sql = 'insert into actor_item (actor, inum) values(?, ?)';
			$this->db->query($sql, array($actor['actor'], $i));
		}
		
		$msg[] = "You smooth the raw lumber into {$a} Wood planks.";
		$cost = $this->ci->skills->getCost('r_planks');
		$ret = $this->ci->actor->spendAP($cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	# wooden arrow =============================================================
	function r_woodarrows(&$actor)
	{
		$pl = $this->ci->actor->getInstanceOf(
			$this->ci->item->getByName('wood plank'), $actor['actor'], 1);
		if(! $pl) return array("This recipe requires 1 Wood plank.");
		$this->ci->actor->dropItems(array($pl), $actor['actor']);
		$cnt = rand(2, 3);
		$a = 0;
		$i = $this->ci->item->getByName('wooden arrow');
		
		for($a = 0; $a < $cnt; $a++)
		{
			$sql = 'insert into actor_item (actor, inum) values (?, ?)';
			$this->db->query($sql, array($actor['actor'], $i));
		}
		
		$msg[] = "You shape the plank into {$a} Wooden arrows.";
		$cost = $this->ci->skills->getCost('r_woodarrows');
		$ret = $this->ci->actor->spendAP($cost['cost_ap'], $actor);
		$this->ci->actor->addXP($actor, 1);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
}
