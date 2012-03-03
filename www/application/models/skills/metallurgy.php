<?php if(! defined('BASEPATH')) exit();

class metallurgy extends NoCacheModel
{
	private $ci;
	
	# constructor
	function metallurgy()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->ci->load->model('item');
	}

	# use skill
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
		$skills =
			$this->ci->actor->getSkills($actor, -1, 'metallurgy', true);
		$ret = array();
		foreach($skills as $s)
			$ret[] = array($s['skill'], "{$s['sname']} ({$s['cost_ap']}AP)");
		return $ret;
	}
	
	function show(&$actor)
	{
		$this->ci->load->model('map');
		return $this->ci->map->cellHasClass('forge', $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors'], $cellinfo);
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('r_leadbar', $actor['actor']);
		$this->ci->actor->addSkill('r_kurumaken', $actor['actor']);
		$this->ci->actor->addSkill('r_copperbar', $actor['actor']);
		$this->ci->actor->addSkill('r_copperarmor', $actor['actor']);
	}
	
# recipes ======================================================================

	# lead bar =================================================================
	function r_leadbar(&$actor)
	{
		$ore = $this->ci->actor->getInstanceOf(
			$this->ci->item->getByName('lead ore'), $actor['actor'], 2);
		if(count($ore) < 2) return array("This recipe requires 2 Lead ore.");
		$this->ci->actor->dropItems($ore, $actor['actor']);
		$sql = 'insert into actor_item (actor, inum) values (?, ?)';
		$this->db->query($sql, array($actor['actor'],
			$this->ci->item->getByName('lead bar')));
		$msg = array();
		$cost = $this->ci->skills->getCost('r_leadbar');
		$ret = $this->ci->actor->spendAP($cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = "You smelt the ore and create a Lead bar.";
		return $msg;
	}
	
	# kurumaken ================================================================
	function r_kurumaken(&$actor)
	{
		$bars = $this->ci->actor->getInstanceOf(
			$this->ci->item->getByName('lead bar'), $actor['actor'], 1);
		if(! $bars) return array("This recipe requires 1 Lead bar.");
		$this->ci->actor->dropItems(array($bars), $actor['actor']);
		$i = $this->ci->item->getByName('kurumaken');
		$cnt = rand(2, 4);
		
		for($a = 0; $a < $cnt; $a++)
		{
			$sql = 'insert into actor_item (actor, inum) values (?, ?)';
			$this->db->query($sql, array($actor['actor'], $i));
		}
		
		$msg = array();
		$cost = $this->ci->skills->getCost('r_kurumaken');
		$ret = $this->ci->actor->spendAP($cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = "You forge the metal into {$cnt} Kurumaken.";
		$this->ci->actor->addXP($actor, 1);
		return $msg;
	}

	# copper bar ===============================================================
	function r_copperbar(&$actor)
	{
		$ore = $this->ci->actor->getInstanceOf(
			$this->ci->item->getByName('copper ore'), $actor['actor'], 2);
		if(count($ore) < 2) return array("This recipe requires 2 Copper ore.");
		$this->ci->actor->dropItems($ore, $actor['actor']);
		$sql = 'insert into actor_item (actor, inum) values (?, ?)';
		$this->db->query($sql, array($actor['actor'],
			$this->ci->item->getByName('copper bar')));
		$msg = array();
		$cost = $this->ci->skills->getCost('r_copperbar');
		$ret = $this->ci->actor->spendAP($cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = "You smelt the ore and create a Copper bar.";
		return $msg;
	}

	# copper armor =============================================================
	function r_copperarmor(&$actor)
	{
		$bars = $this->ci->actor->getInstanceOf(
			$this->ci->item->getByName('copper bar'), $actor['actor'], 3);
		if(count($bars) < 3) return array("This recipe requires 3 Copper bars.");
		$this->ci->actor->dropItems($bars, $actor['actor']);
		$i = $this->ci->item->getByName('copper armor');
		$sql = 'insert into actor_item (actor, inum) values (?, ?)';
		$this->db->query($sql, array($actor['actor'], $i));
		$msg = array();
		$cost = $this->ci->skills->getCost('r_copperarmor');
		$ret = $this->ci->actor->spendAP($cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = "You forge the metal into a suit of Copper armor.";
		$this->ci->actor->addXP($actor, 2);
		return $msg;
	}
}
