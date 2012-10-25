<?php if(! defined('BASEPATH')) exit();

class bldg_upkeep extends Model
{
	private $ci;
	private $binfo;
	
	function bldg_upkeep()
	{
		parent::Model();
		$this->load->database();
		$this->ci =& get_instance();
		$this->ci->load->model('map');
		$this->ci->load->model('actor');
	}
	
	function show()
	{
		return false;
	}
	
	function fire(&$actor, &$retval, $args)
	{
		$inum = $args[0];
		if($inum == 0) return;
		$this->ci->load->model('actor');
		$who = is_array($actor) ? $actor : $this->ci->actor->getInfo($actor);
		$cell = $this->isInsideFriendlyBuilding($who);
		if($cell === false) return;
		if(! $this->binfo['hp'] || $this->binfo['hp'] >= 120) return;
		$ins = $this->ci->actor->getInstanceOf($inum, $who['actor'], 1, true);
		if(! $ins) return array("You don't have such an item.");
		$s = 'update building set hp = hp + ? where map = ? and building = ?';
		$this->db->query($s, array(min(10, 120 - $this->binfo['hp']),
			$who['map'], $cell['building']));
		if($this->db->affected_rows() <= 0)
			return array("There was an error submitting the repairs.");
		$this->ci->actor->dropItems(array($ins), $who['actor']);
		$ret = $this->ci->actor->spendAP(1, $actor);
		foreach($ret as $r)
			$msg[] = $r;
		$msg[] = "You repair the building."; 
		return $msg;
	}
	
	function params(&$actor)
	{
		$who = is_array($actor) ? $actor : $this->ci->actor->getInfo($actor);
		$cell = $this->isInsideFriendlyBuilding($who);
		if($cell === false) return;
		if($this->binfo['hp'] >= 120)
			return array(array(
				'inum' 	=> 0,
				'iname'	=> 'This building does not need repairs.'
				));
		$s = <<<SQL
			select ai.inum, i.iname, count(ai.inum) as num
			from actor_item ai
			join item_class ic on ic.inum = ai.inum
			join class_item ci on ci.iclass = ic.iclass
			join item i on i.inum = ic.inum
			where ci.abbrev = 'construction_mats' and actor = ?
			group by ai.inum
SQL;
		$q = $this->db->query($s, array($who['actor']));
		if($q->num_rows() <= 0)
			return array(array(
				'inum' => 0,
				'iname' => 'You lack the necessary materials.'
				));
		return $q->result_array();
	}
	
	private function isInsideFriendlyBuilding(&$actor)
	{
		if(! $actor['indoors']) return false;
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$this->binfo =
			$this->ci->map->buildingInfo($actor['map'], $cell['building']);
		$owner = $this->ci->actor->getInfo($this->binfo['owner']);
		if($owner['clan'] != $actor['clan']) return false;
		return $cell;
	}
}
