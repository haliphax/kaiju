<?php if(! defined('BASEPATH')) exit();

class NPCModel extends CI_Model
{
	protected $ci;
	protected $abbrev;
	
	function __construct()
	{
		parent::__construct();
		$this->abbrev = substr(get_class($this), 2);
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('actor');
		$this->ci->load->model('effects');
	}

	function spawn($msg = "")
	{
		$s = <<<SQL
			select * from npc_spawn
			where npc = (
				select npc from npc where abbrev = '{$this->abbrev}'
			)
SQL;
		$q = $this->db->query($s);
		$points = $q->result_array();

		$s = <<<SQL
			select actor, aname from actor
			where actor in (
				select actor from actor_npc where npc = (
					select npc from npc where abbrev = '{$this->abbrev}'
				)
			)
				and stat_hp <= 0
SQL;
		$q = $this->db->query($s);
		$r = $q->result_array();
		$point = 0;
		$totpoints = count($points);

		foreach($r as $which)
		{
			$mine = $points[$point];
			$s = <<<SQL
				update actor
				set map = ?, x = ?, y = ?, indoors = ?,
					stat_hp = stat_hpmax, stat_mp = stat_mpmax
				where actor = ?
SQL;
			$this->db->query($s,
				array(
					$mine['map'],
					$mine['x'],
					$mine['y'],
					$mine['indoors'],
					$which['actor']
				)
			);

			if($this->db->affected_rows() <= 0)
				continue;

			$this->ci->map->sendCellEvent(
				str_replace("{0}", $which['aname'], $msg),
				false, $mine['map'], $mine['x'], $mine['y'], $mine['indoors']);
			$this->ci->map->setRadiusEvtM($mine['map'], $mine['x'], $mine['y'], $mine['indoors']);
			$point = ++$point % $totpoints;
		}
	}
}
