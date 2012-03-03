<?php if(! defined('BASEPATH')) exit();

class scanhrzn extends NoCacheModel
{
	private $ci;
	private $cost;
	
	function scanhrzn()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('map');
		$this->ci->load->model('action');
		$this->cost = $this->ci->action->getCost('global', 'scanhrzn');
	}
	
	function fire(&$actor, &$retval)
	{
		if($actor['indoors'] || $actor['stat_hp'] <= 0) return;
		$where = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		
		if(! $this->ci->map->cellHasClass('scan', $actor['map'],
			$actor['x'], $actor['y'], $actor['indoors']))
		{
			return;
		}
		
		$this->ci->load->model('actor');
		$msg = array();
		$msg[] = 'You scan the horizon.';
		$ret = $this->ci->actor->spendAP($this->cost, $actor);
		$this->ci->actor->setStatFlag($actor['actor']);
		foreach($ret as $r) $msg[] = $r;
		$retval['cells'] = $this->ci->map->getMap($actor['map'], $actor['x'],
			$actor['y'], $actor['indoors'], $where['dense'], 4);
		return $msg;
	}
	
	function show(&$actor)
	{
		if($actor['indoors']
			|| ! $this->ci->map->cellHasClass('scan', $actor['map'],
				$actor['x'], $actor['y'], $actor['indoors']))
		{
			return false;
		}
		
		return true;
	}
}