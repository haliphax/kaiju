<?php if(! defined('BASEPATH')) exit();

class sh_establish extends NoCacheModel
{
	private $ci;
	
	function sh_establish()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('map');
		$this->ci->load->model('clan');
	}
	
	function fire(&$actor)
	{
		if(! $this->show($actor)) return false;
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		
		if(! $this->ci->clan->setStronghold($actor['clan'], $actor['map'],
			$cell['building']))
		{
			return array("Error establishing stronghold.");
		}
		
		$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'], $actor['y']);
		$this->ci->map->setCellEvts($actor['map'], $actor['x'], $actor['y'], 0);
		$this->ci->map->setCellEvts($actor['map'], $actor['x'], $actor['y'], 1);
		$this->ci->clan->sendEvent(
			"<b>Your clan has established a stronghold at [{$actor['x']},{$actor['y']}].</b>",
			$actor['clan']);
		return;
	}
	
	function show(&$actor)
	{
		if(! $actor['indoors'] || ! $actor['clan']) return false;
		$clan = $this->ci->clan->getInfo($actor['clan']);
		if($clan['x'] || $clan['leader'] != $actor['actor']) return false;
		$this->ci->load->model('map');
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		if(! $cell['building']) return false;
		$bldg = $this->ci->map->buildingInfo($actor['map'], $cell['building']);
		if($bldg['owner'] != $actor['actor']) return false;
		if($this->ci->clan->capturedRecently($actor['clan'])) return false;
		$occupants = $this->ci->map->getCellOccupants($actor['map'],
			$actor['x'], $actor['y'], 1);
		foreach($occupants as $occupant)
			if($occupant['faction'] != $actor['faction'])
				return false;
		return true;
	}
}