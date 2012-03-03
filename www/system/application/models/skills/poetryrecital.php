<?php if(! defined('BASEPATH')) exit();

class poetryrecital extends Model
{
	private $ci;
	private $cost;
	
	# constructor
	function poetryrecital()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('poetryrecital');
	}

	# use skill
	function fire(&$actor)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->skills->noap;
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->skills->nomp;
		$this->ci->load->model('pdata');
		$msg = array();
		$ret = $this->ci->actor->addEffect('exertion', $actor);
		foreach($ret as $r) $msg[] = $r;
		$cnt = $this->ci->pdata->get('effect', 'exertion', $actor['actor']);
		
		if($cnt < 16)
		{
			$cnt += 5;
			$this->ci->pdata->set('effect', 'exertion', $cnt, $actor['actor']);
		}
		else		
			return array("You are far too fatigued for reciting poetry.");
		
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] =
			"You recite an elegant and timeless poem. Nearby allies' pains are somewhat alleviated.";
		$this->ci->load->model('map');
		$this->ci->map->sendCellEvent(
			"{$actor['aname']} recited an elegant and timeless poem.",
			array($actor['actor']), $actor['map'], $actor['x'], $actor['y'],
			$actor['indoors']);
		$occs = $this->ci->map->getCellOccupants($actor['map'], $actor['x'],
			$actor['y'], $actor['indoors']);
		
		foreach($occs as $occ)
			if($occ['actor'] != $actor['actor']
				&& $occ['faction'] == $actor['faction'])
			{
				$heal['hp'] = 2;
				$victim = $this->ci->actor->getInfo($occ['actor']);
				$down = $victim['stat_hpmax'] - $victim['stat_hp'];
				if($down <= 0)
					continue;
				else if($down < $heal['hp'])
					$heal['hp'] = $down;
				$ret = $this->ci->actor->heal($actor, $victim, &$heal);
				foreach($ret as $r) $msg[] = $r;
				$this->ci->actor->sendEvent(
					"You were healed for {$heal['hp']}HP.", $victim['actor']);				
			}
		
		return $msg;
	}
	
	function show(&$actor)
	{
		return true;
	}
}
