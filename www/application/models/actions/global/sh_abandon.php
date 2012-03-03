<?php if(! defined('BASEPATH')) exit();

class sh_abandon extends NoCacheModel
{
	private $ci;
	
	function sh_abandon()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function fire(&$actor)
	{
		if(! $this->show($actor)) return false;
		if(! $this->ci->clan->clearStronghold($actor['clan']))
			return array("Error abandoning stronghold.");
		$s = <<<SQL
			insert into clan_capture (clan, rclan, stamp)
			values (?, ?, UNIX_TIMESTAMP())
SQL;
		$this->db->query($s, array($actor['clan'], $actor['clan']));
		$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'], $actor['y']);
		$this->ci->map->setCellEvts($actor['map'], $actor['x'], $actor['y'], 0);
		$this->ci->map->setCellEvts($actor['map'], $actor['x'], $actor['y'], 1);
		$this->ci->clan->sendEvent(
			"<b>Your clan's stronghold has been abandoned!</b>",
			$actor['clan']);
		return;
	}
	
	function show(&$actor)
	{
		if(! $actor['indoors'] || ! $actor['clan']) return false;
		$this->ci->load->model('clan');
		$clan = $this->ci->clan->getInfo($actor['clan']);
		if(! $clan['x'] || $clan['leader'] != $actor['actor']) return false;
		$this->ci->load->model('map');
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		if(! $cell['building']) return false;
		$bldg = $this->ci->map->buildingInfo($actor['map'], $cell['building']);
		if($bldg['owner'] != $actor['actor']) return false;
		
		if($actor['map'] != $clan['map']
			|| $cell['building'] != $clan['building'])
		{
			return false;
		}
		
		return true;
	}
}