<?php if(! defined('BASEPATH')) exit();

class sh_reclaim extends CI_Model
{
	private $ci;
	private $cost;

	function sh_reclaim()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('action');
		$this->ci->load->model('map');
		$this->ci->load->model('clan');	
		$this->cost = $this->ci->action->getCost('global', 'sh_reclaim');
	}
	
	function fire(&$actor)
	{
		if(! $this->show($actor))
			return;
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$this->load->database();
		$s = <<<SQL
			update clan_capture set reclaimed = b'1'
			where rclan = ? and clan = ?
			order by stamp desc
			limit 1
SQL;
		$this->db->query($s, array($actor['clan'], $cell['clan']));
		if($this->db->affected_rows() <= 0)
			return array('Error reclaiming standard.');
		$myclan = $this->ci->clan->getInfo($actor['clan']);
		$theirclan = $this->ci->clan->getInfo($cell['clan']);
		$this->ci->clan->sendEvent(
			"<b>{$actor['aname']} has reclaimed your clan's standard from {$theirclan['descr']}!</b>",
			$actor['clan'], array($actor['actor']));
		$this->ci->clan->sendEvent(
			"<b>{$actor['aname']} has reclaimed the clan standard of {$myclan['descr']}!</b>",
			$theirclan['clan']);
		$this->ci->map->setCellEvts($actor['map'], $actor['x'], $actor['y'], 1);
		return array("<b>You have reclaimed your clan's standard from {$theirclan['descr']}!</b>");
	}
	
	function show(&$actor)
	{
		if(! $actor['indoors'] || ! $actor['clan'])
			return false;
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
			
		if(! $cell['building'] || ! $cell['clan']
			|| $cell['clan'] == $actor['clan'])
		{
			return false;
		}
		
		$myclan = $this->ci->clan->getInfo($actor['clan']);
		$theirclan = $this->ci->clan->getInfo($cell['clan']);
		
		if(! $this->ci->clan->capturedRecently($myclan['clan'],
			$theirclan['clan']))
		{
			return false;
		}
		
		$occupants = $this->ci->map->getCellOccupants($actor['map'],
			$actor['x'], $actor['y'], 1, 1);
		foreach($occupants as $occupant)
			if($occupant['faction'] != $actor['faction'])
				return false;
		return true;
	}
}