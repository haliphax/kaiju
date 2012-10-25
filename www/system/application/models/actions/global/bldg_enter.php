<?php if(! defined('BASEPATH')) exit();

class bldg_enter extends Model
{
	private $ci;
	
	function bldg_enter()
	{
		parent::Model();
		$this->ci =& get_instance();
	}
	
	function fire(&$actor)
	{
		if(! $this->show($actor)) return;
		$this->ci->load->model('map');
		$map = $actor['map'];
		$x = $actor['x'];
		$y = $actor['y'];
		$cellinfo = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$b = $cellinfo['building'];
		$structure = $this->ci->map->getBuildingStructure($map, $b);
		$sql = <<<SQL
			select abbrev from building_trigger bt
				where map = ? and building = ? and enter = b'1'
			union
			select cs.abbrev from building_class bc
				join class_structure cs on bc.bclass = cs.sclass
				join class_structure_trigger cst on cs.sclass = cst.sclass
				where map = ? and building = ? and
				(indoors = 1 or indoors = -1)
				and enter = b'1'
			union
			select cs.abbrev as type from structure_class sc
				join class_structure cs on sc.sclass = cs.sclass
				join class_structure_trigger cst on cs.sclass = cst.sclass
				where structure = ? and
				(indoors = 1 or indoors = -1)
				and enter = b'1'
SQL;
		$q = $this->db->query($sql, array($map, $b, $map, $b, $structure));
		$r = $q->result_array();
		$allowed = false;
		$ret = array();
		
		foreach($r as $row)
		{
			$which = "st_{$row['abbrev']}";
			
			$this->ci->load->model("classes/structure/{$which}");
			$allowed = call_user_func(
				array($this->ci->$which, 'b_enter'), $actor);
			foreach($allowed[1] as $m) $ret[] = $m;
			if($allowed[0] === false) return $ret;
		}
		
		$this->ci->load->model('action');
		$res = $this->ci->action->indoors(&$actor, &$succ, 1);
		if($res === false) return;
		if($succ) $ret[] = "You go inside.";
		foreach($res as $r) $ret[] = $r;
		return $ret;
	}
	
	function show(&$actor)
	{
		$this->ci->load->model('actor');
		$this->ci->load->model('map');
		if($actor['indoors'] || $this->ci->actor->isElevated($actor['actor']))
			return false;
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		if(! $cell['building']) return false;
		
		if($this->ci->map->tileIsUnderConstruction($actor['map'],
			$cell['building']))
		{
			return false;
		}
		
		if($cell['clan'])
		{
			if($cell['clan'] == $actor['clan']) return true;
			$this->ci->load->model('clan');
			$shield = $this->ci->clan->getStrongholdShield($cell['clan']);
			if($shield == 0) return true;
			if($this->ci->clan->isAllyOf($actor['clan'], $cell['clan']))
				return true;
			return false;
		}
		
		return true;
	}
}