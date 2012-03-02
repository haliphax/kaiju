<?php if(! defined('BASEPATH')) exit();

class e_decayed extends CI_Model
{
	private $ci;
	
	function e_decayed()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}

	function on(&$actor)
	{
		# locate nearest graveyard and move corpse
		$sql = <<<SQL
			select x, y, sqrt(pow(? - x, 2) + pow(? - y, 2)) as d
				from map_cell mc
			join tile t on mc.tile = t.tile
			where t.descr = 'Graveyard' and map = ?
			order by d asc
			limit 1
SQL;
		$q = $this->db->query($sql, array($actor['x'], $actor['y'],
			$actor['map']));
		$r = $q->row_array();
		$sql = 'update actor set x = ?, y = ?, indoors = 0 where actor = ?';
		$this->db->query($sql, array($r['x'], $r['y'], $actor['actor']));
	}
}