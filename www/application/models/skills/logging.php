<?php if(! defined('BASEPATH')) exit();

class logging extends NoCacheModel
{
	private $ci;
	private $cost;
	
	# constructor
	function logging()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->ci->load->model('item');
		$this->cost = $this->ci->skills->getCost('logging');
	}

	# use skill
	function fire(&$actor)
	{
		$c = 0;
		if(! $this->show($actor)) return false;
		if($this->ci->actor->isOverEncumbered($actor['actor']))
			return array("You are too encumbered for such hard work.");
		
		if($this->ci->map->cellHasClass('lumber_dense', $actor['map'],
			$actor['x'], $actor['y'], $actor['indoors']))
		{
			$c = 7;
		}
		else
			$c = 5;
		
		$roll = rand(1, 20);
		$msg = array();
		
		if($roll <= $c)
		{
			$i = $this->ci->item->getByName('lumber');
			$sql = 'insert into actor_item (actor, inum) values (?, ?)';
			$this->db->query($sql, array($actor['actor'], $i));
			$msg[] =
				"You managed to fell a decent-sized tree and gather lumber.";
		}
		else
			$msg[] =
				"You spend some time searching for a tree worth chopping, but find none.";
		
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	# show skill?
	function show(&$actor)
	{
		$this->ci->load->model('map');
		
		if(! $this->ci->map->cellHasClass('lumber', $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors']))
		{
			return false;
		}
		
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		foreach($weps as $w)
			if($w['iname'] == 'Wood axe') return true;
		return false;
	}
}
