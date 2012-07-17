<?php if(! defined('BASEPATH')) exit();

class mining extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->ci->load->model('item');
		$this->cost = $this->ci->skills->getCost('mining');
	}

	# use skill
	function show(&$actor)
	{
		$this->ci->load->model('map');
		
		if(! $this->ci->map->cellHasClass('ore', $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors']))
		{
			return false;
		}
		
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		foreach($weps as $w)
			if($w['iname'] == 'Mining pick') return true;
		return false;
	}
	
	function fire(&$actor)
	{
		if($this->ci->actor->isOverEncumbered($actor['actor']))
			return array("You are too encumbered for such hard work.");
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$classes = $this->ci->map->getCellClasses($actor['map'], $actor['x'],
				$actor['y'], $actor['indoors']);
		shuffle($classes);
		$roll = rand(1, 20);
		$nada = array("You pick at the rock, but find nothing of interest.");
		$chance = 5; # 25% chance
		$found = 0;
		$msg = array();
		
		foreach($classes as $class)
		{
			$break = false;
			
			switch($class)
			{
				case 'ore':
				{
					if($roll > $chance)
						return $nada;
					else if($roll == $chance)
						$found = 'Copper ore';
					else
						$found = 'Lead ore';
					$break = true;
					break;
				}
			}
			
			if($found)
			{
				$msg[] =
					"You managed to mine a(n) <b>{$found}</b> from the rock.";
				$found = $this->ci->item->getByName($found);
			}
			
			if($break === true) break;
		}
		
		$sql = 'insert into actor_item (actor, inum) values (?, ?)';
		$this->db->query($sql, array($actor['actor'], $found));
		return $msg;
	}
}
