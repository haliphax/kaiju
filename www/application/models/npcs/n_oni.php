<?php if(! defined('BASEPATH')) exit();

class n_oni extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('map');
	}
	
	function spawn()
	{
		$s = <<<SQL
			select actor.actor, aname, map, x, y, indoors from actor
			join actor_npc on actor.actor = actor_npc.actor
			join npc on actor_npc.npc = npc.npc
			where npc.abbrev = 'oni' and stat_hp <= 0
				and last < UNIX_TIMESTAMP() - 300
SQL;
		$q = $this->db->query($s);
		$r = $q->result_array();
		$s = <<<SQL
			update actor set map = 0 - map, stat_hp = stat_hpmax
			where stat_hp <= 0 and actor in (
				select actor from actor_npc where npc = (
					select npc from npc where abbrev = 'oni'
				)
			)
SQL;
		$this->db->query($s);
		
		foreach($r as $row)
		{
			$this->ci->map->sendCellEvent(
				"{$row['aname']} appears from beyond the void.",
				false, $row['map'], $row['x'], $row['y'], $row['indoors']);
			$this->ci->map->setRadiusEvtM($row['map'], $row['x'], $row['y']);
		}
	}
	
	function tick($tick)
	{
	}
	
	function defend(&$victim, &$actor, &$swing)
	{
	}
}