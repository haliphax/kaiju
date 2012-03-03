<?php if(! defined('BASEPATH')) exit();

class bldg_exit extends NoCacheModel
{
	private $ci;
	
	function bldg_exit()
	{
		parent::__construct();
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
			select cs.abbrev from building_class bc
				join class_structure cs on bc.bclass = cs.sclass
				join class_structure_trigger cst on cs.sclass = cst.sclass
				where map = ? and building = ? and
				(indoors = ? or indoors = -1)
				and enter = b'1'
			union
			select cs.abbrev from structure_class sc
				join class_structure cs on sc.sclass = cs.sclass
				join class_structure_trigger cst on cs.sclass = cst.sclass
				where structure = ? and
				(indoors = ? or indoors = -1)
				and enter = b'1'
SQL;
		$q = $this->db->query($sql, array($map, $b, 1, $structure, 1));
		$r = $q->result_array();
		$allowed = false;
		$ret = array();
		
		foreach($r as $row)
		{
			$which = "st_{$row['abbrev']}";
			$this->ci->load->model("classes/structure/{$which}");
			$allowed = call_user_func(
				array($this->ci->$which, 'b_exit'), $actor);
			foreach($allowed[1] as $m) $ret[] = $m;
			if($allowed[0] === false) return $ret;
		}
		
		$this->ci->load->model('action');
		$res = $this->ci->action->indoors(&$actor, &$succ, 0);
		if($res === false) return;
		if($succ) $ret[] = "You go outside.";
		foreach($res as $r) $ret[] = $r;
		return $ret;
	}
	
	function show(&$actor)
	{
		$this->ci->load->model('actor');
		if(! $actor['indoors'] || $this->ci->actor->isElevated($actor['actor']))
			return false;
		$this->ci->load->model('map');
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		if(! $cell['building']) return false;
		return true;
	}
}