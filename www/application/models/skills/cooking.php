<?php if(! defined('BASEPATH')) exit();

class cooking extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->ci->load->model('item');
	}

	# use skill
	function fire(&$actor, $args)
	{
		$recipe = $args[0];
		$r = $this->ci->skills->getInfo($recipe);
		$which = $r['abbrev'];
		return $this->$which($actor);
	}
	
	function params(&$actor)
	{
		$skills =
			$this->ci->actor->getSkills($actor, -1, 'cooking', true);
		$ret = array();
		foreach($skills as $s)
			$ret[] = array($s['skill'], "{$s['sname']} ({$s['cost_ap']}AP)");
		return $ret;
	}
	
	function show(&$actor)
	{
		$this->ci->load->model('map');
		return $this->ci->map->cellHasClass('stove', $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors'], $cellinfo);
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('r_steamedrice', $actor['actor']);
		$this->ci->actor->addSkill('r_sushi', $actor['actor']);
	}
	
# recipes ======================================================================

	function r_steamedrice(&$actor)
	{
		$bars = $this->ci->actor->getInstanceOf(
			$this->ci->item->getByName('uncooked rice'), $actor['actor'], 1);
		if(! $bars) return array("This recipe requires 1 Uncooked rice.");
		$this->ci->actor->dropItems(array($bars), $actor['actor']);
		$i = $this->ci->item->getByName('steamed rice');
		$cnt = rand(2, 3);
		
		for($a = 0; $a < $cnt; $a++)
		{
			$sql = 'insert into actor_item (actor, inum) values (?, ?)';
			$this->db->query($sql, array($actor['actor'], $i));
		}
		
		$msg = array();
		$cost = $this->ci->skills->getCost('r_steamedrice');
		$ret = $this->ci->actor->spendAP($cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = "You cook the rice into {$cnt} portions.";
		return $msg;
	}

	function r_sushi(&$actor)
	{
		$rice = $this->ci->actor->getInstanceOf(
			$this->ci->item->getByName('steamed rice'), $actor['actor'], 1);
		$fish = $this->ci->actor->getInstanceOf(
			$this->ci->item->getByName('raw fish'), $actor['actor'], 1);
		if(! $rice || ! $fish) return array("This recipe requires 1 steamed rice and 1 raw fish.");
		$this->ci->actor->dropItems(array($rice, $fish), $actor['actor']);
		$i = $this->ci->item->getByName('sushi');
		$cnt = rand(2, 3);
		
		for($a = 0; $a < $cnt; $a++)
		{
			$sql = 'insert into actor_item (actor, inum) values (?, ?)';
			$this->db->query($sql, array($actor['actor'], $i));
		}
		
		$msg = array();
		$cost = $this->ci->skills->getCost('r_sushi');
		$ret = $this->ci->actor->spendAP($cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = "You craft the ingredients into {$cnt} portions of sushi.";
		$this->ci->actor->addXP($actor, 1);
		return $msg;
	}
}
