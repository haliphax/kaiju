<?php if(! defined('BASEPATH')) exit();

class illuminate extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function illuminate()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('illuminate');
	}

	# use skill
	function fire(&$actor)
	{
		if(! $this->show($actor)) return false;
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		$s = <<<SQL
			select a.actor as actor, aname from actor a
			join actor_effect ae on a.actor = ae.actor
			join effect e on ae.effect = e.effect
			where map = ? and x = ? and y = ? and indoors = ?
				and a.actor != ? and e.abbrev = 'hiding'
			order by rand()
			limit 3
SQL;
		$q = $this->db->query($s, array($actor['map'], $actor['x'],
			$actor['y'], $actor['indoors'], $actor['actor']));
		$this->ci->load->model('map');
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$this->ci->map->sendCellEvent(
			"A bright light bursts forth from {$actor['aname']}'s fingertips, "
			. "illuminating the area.", array($actor['actor']), $actor['map'],
			$actor['x'], $actor['y'], $actor['indoors']);
		$msg = array(
			"A bright light bursts forth from your fingertips, illuminating the area."
			);
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($this->cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$res = $q->result_array();
		
		foreach($res as $row)
		{
			$this->ci->actor->removeEffect('hiding', $row['actor']);
			$msg[] = "You discovered {$row['aname']} hiding in the shadows!";
			$this->ci->actor->sendEvent(
				"Light shone on your hiding spot, revealing your location!",
				$row['actor']);
		}
		
		$this->ci->map->setCellEvts($actor['map'], $actor['x'], $actor['y'],
			$actor['indoors']);
		$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'], $actor['y'],
			$cell['building']);
		return $msg;
	}
	
	function show(&$actor)
	{
		return $actor['indoors'] == 1;
	}
}
