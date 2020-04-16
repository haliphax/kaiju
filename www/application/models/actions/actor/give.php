<?php if(! defined('BASEPATH')) exit();

class give extends CI_Model
{
	private $ci;
	private $cost;
	
	function give()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('action');
		$this->cost = $this->ci->action->getCost('actor', 'give');
		$this->load->database();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
	}
	
	function fire(&$actor, &$retval, $args)
	{
		$tar = $this->ci->actor->getInfo($args[0]);
		if(! $this->show($actor, $tar))
			return;
		
		$i = $args[1];
		$ins = $this->ci->actor->getInstanceOf($i, $actor['actor'], 1, true);
		if(! $ins) return array("You can't give what you don't have.");
		$this->ci->load->model('item');
		$item = $this->ci->item->getInfo($ins);
		if(! $item) return array("Unknown item: {$i}.");
		
		if($this->ci->actor->isOverEncumbered($tar['actor']) ||
			$this->ci->actor->wouldBeOverEncumbered($tar['actor'], $item['weight']))
		{
			return array("They do not have room in their inventory.");
		}
		
		$sql =
			'update actor_item set actor = ? where actor = ? and instance = ?';
		$this->db->query($sql, array($tar['actor'], $actor['actor'], $ins));
		$this->ci->actor->sendEvent(
			"{$actor['aname']} gave you a(n) {$item['iname']}.", $tar['actor']);
		$msg[] = "You gave a(n) {$item['iname']} to {$tar['aname']}.";
		$ret = $this->ci->actor->spendAP($this->cost, $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function params(&$actor)
	{
		$res = $this->ci->actor->getItems($actor['actor']);
		$p = array();
		foreach($res as $r)
			if(! $r['eq_slot'])
				$p[] = array($r['inum'], $r['iname']);
		return $p;
	}
	
	
	function show(&$actor, &$victim)
	{
		if($victim['actor'] <= 0) return false;
		if(! $this->params($actor)) return false;
		if($this->ci->actor->hasEffect('noinventory', $victim['actor'])) return false;
		
		if($this->ci->skills->canMelee($victim['actor'], $actor['actor'],
			'melee'))
		{
			return true;
		}
		
		return false;
	}
}
