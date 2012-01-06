<?php if(! defined('BASEPATH')) exit();

class attackbarrier extends Model
{
	private $ci;
	
	function attackbarrier()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('map');
	}
	
	function show(&$actor)
	{
		if($actor['indoors']) return false;
		$cellinfo = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		if(! $cellinfo['clan']) return false;
		if($cellinfo['clan'] === $actor['clan']) return false;
		$this->ci->load->model('clan');
		if($this->ci->clan->getStrongholdShield($cellinfo['clan']) <= 0)
			return false;
		return true;
	}
}