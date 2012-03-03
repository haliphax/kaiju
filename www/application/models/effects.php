<?php if(! defined('BASEPATH')) exit();

class effects extends NoCacheModel
{
	private $ci;
	
	function effects()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->load->database();
	}
	
	# pull effect's description
	function getDescription($effect)
	{
		$sql = 'select descr, ename from effect where effect = ?';
		$query = $this->db->query($sql, array($effect));
		return $query->row_array();
	}
	
	# get actors with particular effect (for ticks)
	function getActorsWith($effect)
	{
		$sql = <<<SQL
			select a.actor, aname, a.map, a.x, a.y, tile, indoors, building,
				faction, stat_hp
				from effect e
			join actor_effect ae on e.effect = ae.effect
			join actor a on ae.actor = a.actor
			join map_cell c on a.map = c.map and a.x = c.x and a.y = c.y
			where abbrev = ? and a.stat_hp > 0
			order by map asc, x asc, y asc, indoors asc
SQL;
		$query = $this->db->query($sql, array($effect));
		return $query->result_array();
	}
}