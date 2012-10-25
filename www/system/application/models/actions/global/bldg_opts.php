<?php if(! defined('BASEPATH')) exit();

class bldg_opts extends Model
{
	private $ci;
	
	function bldg_opts()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('map');
		$this->ci->load->model('actor');
	}
	
	function fire(&$actor)
	{
		if(! $actor['indoors']) return;
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		if(! $cell['building']) return;
		$q = $this->db->query(
			'select owner, descr from building where map = ? and building = ?',
			array($actor['map'], $cell['building']));
		$r = $q->row_array();
		if($r['owner'] != $actor['actor']) return;
		$name = preg_replace("#[^- _:,/\"'~a-z0-9]#i", '',
			$this->input->post('name'));
		if($name == '') return;
		$s = <<<SQL
			update building set descr = ?, surr_i = ?, surr = ?
			where map = ? and building = ?
SQL;
		$idescr = htmlentities($this->input->post('idescr'));
		$odescr = htmlentities($this->input->post('odescr'));
		$this->db->query($s, array($name, $idescr, $odescr, $actor['map'],
			$cell['building']));
		$this->ci->actor->setStatFlag($actor['actor']);
	}
	
	function params(&$actor)
	{
		if(! $this->show($actor)) return;
		$who = is_array($actor) ? $actor : $this->ci->actor->getInfo($actor);
		$cell = $this->ci->map->getCellInfo($who['map'], $who['x'],
			$who['y']);
		$r = $this->ci->map->buildingInfo($who['map'], $cell['building']);
		if($r['owner'] != $who['actor']) return;
		$ret['name'] = $r['descr'];
		$ret['idescr'] = html_entity_decode($r['surr_i']);
		$ret['odescr'] = html_entity_decode($r['surr']);
		return $ret;
	}
	
	function show(&$actor)
	{
		$who = is_array($actor) ? $actor : $this->ci->actor->getInfo($actor);
		if(! $who['indoors']) return false;
		$cell = $this->ci->map->getCellInfo($who['map'], $who['x'],
			$who['y']);
		if(! $cell['building']) return false;
		$r = $this->ci->map->buildingInfo($who['map'], $cell['building']);
		if(! $r['owner']) return false;
		$owner = $this->ci->actor->getInfo($r['owner']);
		if($who['clan'] && ($owner['clan'] != $who['clan'])) return false;
		else if($owner['actor'] != $who['actor']) return false;
		return true;
	}
}