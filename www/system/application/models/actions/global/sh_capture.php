<?php if(! defined('BASEPATH')) exit();

class sh_capture extends Model
{
	private $ci;
	
	function sh_capture()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('map');
		$this->ci->load->model('clan');
	}
	
	function fire(&$actor)
	{
		if(! $this->show($actor))
			return;
		$this->load->database();
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$this->db->query('delete from clan_stronghold where clan = ?',
			$cell['clan']);
		if($this->db->affected_rows() <= 0)
			return array('Error capturing standard.');
		$s = <<<SQL
			insert into clan_capture (clan, rclan, stamp)
			values (?, ?, UNIX_TIMESTAMP())
SQL;
		$this->db->query($s, array($actor['clan'], $cell['clan']));
		if(! is_int($this->db->insert_id()))
			return array('Error capturing standard.');
		$clan = $this->ci->clan->getInfo($cell['clan']);
		$this->ci->map->sendCellEvent(
			"{$actor['aname']} has captured the clan standard of {$clan['descr']}!",
			array($actor['actor']), $cell['map'], $cell['x'], $cell['y'], 1
			);
		$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'], $actor['y']);
		$this->ci->map->setCellEvts($actor['map'], $actor['x'], $actor['y'], 0);
		$this->ci->map->setCellEvts($actor['map'], $actor['x'], $actor['y'], 1);
		$this->ci->clan->sendEvent(
			"<b>{$actor['aname']} has captured the clan standard of {$clan['descr']}!</b>",
			$actor['clan'], array($actor['actor']));
		$this->ci->clan->sendEvent(
			"<b>{$actor['aname']} has captured your clan's standard!</b>",
			$clan['clan']);
		return
			array("<b>You have captured the clan standard of {$clan['descr']}!</b>");
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
		if($myclan['faction'] == $theirclan['faction'])
			return false;
		$occupants = $this->ci->map->getCellOccupants($actor['map'],
			$actor['x'], $actor['y'], 1, 1);
		foreach($occupants as $occupant)
			if($occupant['faction'] != $actor['faction'])
				return false;
		return true;
	}
}