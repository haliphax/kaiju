<?php if(! defined('BASEPATH')) exit();

class annex extends SkillModel
{
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->ci->load->model('map');
		$this->cost = $this->ci->skills->getCost('annex');
	}

	# use skill
	function fire(&$actor, $args)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return array("You don't have the energy right now.");
		$cell = $this->ci->map->getCellInfo(
			$actor['map'], $actor['x'], $actor['y']);
		if(! $cell['building'])
			return array('There is no building here to annex.');
		$building = $this->ci->map->buildingInfo(
			$actor['map'], $cell['building']);
		if($building['owner'] != $actor['actor'])
			return array('This is not your building to annex.');

		$old = $cell['building'];	
		$x = $args[0];
		$y = $args[1];
		$cell = $this->ci->map->getCellInfo(
				$actor['map'], $x, $y);
		if(! $cell['building'])
			return array('There is no such building.');
		$building = $this->ci->map->buildingInfo(
			$actor['map'], $cell['building']);
		if($building['owner'] != $actor['actor'])
			return array('You do not own that building.');

		$s = <<<SQL
			select count(1) as c from map_cell
			where map = ? and (building = ? or building = ?)
SQL;
		$res = $this->db->query($s,
			array($actor['map'], $old, $cell['building'])
		);

		$res = $res->row_array();
		if($res['c'] > 4)
			return array("You lack the skill for such a large project.");

		$tables = array(
			'action_building',
			'building_class',
			'building_search',
			'building_trigger',
			'clan_stronghold',
			'map_cell'
		);

		$s = <<<SQL
			update %s
			set building = ?
			where map = ? and building = ?
SQL;

		foreach($tables as $table)
		{
			$this->db->query(sprintf($s, $table),
				array($cell['building'], $actor['map'], $old)
			);
		}

		$s = <<<SQL
			delete from %s
			where map = ? and building = ?
SQL;

		foreach($tables as $table)
		{
			$this->db->query(sprintf($s, $table),
				array($actor['map'], $old)
			);
		}

		$this->db->query(
			'delete from building where map = ? and building = ?',
			array($actor['map'], $old)
		);

		$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'], $actor['y'],
			false, 4);
		$msg = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor); 
		$msg[] = "Building annexed.";
		return $msg;
	}
	
	# skill parameters
	function params(&$actor)
	{
		$cell = $this->ci->map->getCellInfo(
			$actor['map'], $actor['x'], $actor['y']);
		if(! $cell['building'])
			return false;
		$building = $this->ci->map->buildingInfo(
			$actor['map'], $cell['building']);
		if($building['owner'] != $actor['actor'])
			return false;
		$adjacents = array();
		$cells = array(
			array($actor['x'] - 1, $actor['y']),
			array($actor['x'] + 1, $actor['y']),
			array($actor['x'], $actor['y'] - 1),
			array($actor['x'], $actor['y'] + 1)
			);
		$origin = $cell['building'];

		foreach($cells as $c)
		{
			$cell = $this->ci->map->getCellInfo($actor['map'], $c[0], $c[1]);
			if(! $cell['building'])
				continue;
			if($cell['building'] == $origin)
				continue;
			$building = $this->ci->map->buildingInfo(
				$actor['map'], $cell['building']);
			if($building['owner'] != $actor['actor'])
				continue;
			if($building['descr'] == '')
				$building['descr'] = $this->ci->map->siteStructureName(
					$actor['map'], $cell['building']);
			$adjacents[] = array(
				"{$c[0]}/{$c[1]}", "{$building['descr']} [{$c[0]},{$c[1]}]");
		}
		
		if(count($adjacents) == 0)
			$adjacents = false;
		return $adjacents;
	}
	
	function show(&$actor)
	{
		if(! $this->params($actor))
			return false;
		return true;
	}
}
