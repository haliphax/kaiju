<?php if(! defined('BASEPATH')) exit();

class scavenge extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}

	# use skill
	function fire(&$actor)
	{
		if($this->ci->actor->isOverEncumbered($actor['actor']))
			return array("You have no room in your inventory.");
		$classes = $this->ci->map->getCellClasses($actor['map'], $actor['x'],
				$actor['y'], $actor['indoors']);
		shuffle($classes);
		$roll = rand(1, 20);
		$nada = array("You could not manage to scavenge anything of interest.");
		$chance = 3;
		$found = 0;
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		
		foreach($classes as $class)
		{
			$break = false;
			
			switch($class)
			{
				case 'ore':
				{
					if($roll > $chance) return $nada;
					$found = 'Lead Ore';
					$break = true;
					break;
				}				
				case 'lumber':
				{
					if($roll > $chance) return $nada;
					$found = 'Lumber';
					$break = true;
					break;
				}
			}
			
			if($found)
			{
				$this->ci->load->model('item');
				$msg[] =
					"Your efforts were fruitful. You scavenged a(n) {$found}.";
				$found = $this->ci->item->getByName($found);
			}
			
			if($break === true) break;
		}
		
		$sql = 'insert into actor_item (actor, inum) values (?, ?)';
		$this->db->query($sql, array($actor['actor'], $found));
		return $msg;	
	}
	
	# show skill?
	function show(&$actor)
	{
		$this->ci->load->model('map');
		
		if($this->ci->map->cellHasClass('scavenge', $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors']))
		{
			return true;
		}
		
		return false;
	}
}
