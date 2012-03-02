<?php if(! defined('BASEPATH')) exit();

class repair extends CI_Model
{
	private $ci;
	
	# constructor
	function repair()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
	}

	# use skill
	function fire(&$actor, $args)
	{
		$item = $args[0];
		$sql = <<<SQL
			select ai.durability as durability, durmax, iname
				from actor_item ai
			join item i on ai.inum = i.inum
			where ai.instance = ? and ai.actor = ?
				and ai.durability is not null
			limit 1
SQL;
		$q = $this->db->query($sql, array($item, $actor['actor']));
		if($q->num_rows() <= 0)
			return array("You have no such item in need of repair.");
		$r = $q->row_array();
		$repaired = $r['durmax'] - $r['durability'] - 1;
		if($repaired <= 0)
			return array("The item is already in perfect condition.");
		$sql = <<<SQL
			update actor_item set durability = durmax - 1, durmax = durmax - 1
			where instance = ?
SQL;
		$this->db->query($sql, array($item));
		$ret = array("You repaired the {$r['iname']} by {$repaired} points.");
		$msg = $this->ci->actor->spendAP(round(2.5 * ($repaired + 1)), $actor);
		$this->ci->actor->addXP($actor, round($repaired / 4));
		foreach($msg as $m) $ret[] = $m;
		return $ret;
	}
	
	# skill parameters
	function params($actor)
	{
		$sql = <<<SQL
			select instance, iname, ai.durability, durmax from actor_item ai
			join item i on ai.inum = i.inum
			where ai.actor = ? and eq_type is not null
				and ai.durability is not null
				and ai.durability < ai.durmax - 1
SQL;
		$q = $this->db->query($sql, array($actor['actor']));
		$r = $q->result_array();
		$res = array();
		
		foreach($r as $row)
		{
			$ap = round(2.5 * ($row['durmax'] - $row['durability']));
			if($ap < 0) $ap = 0;
			$res[] = array($row['instance'],
				"{$row['iname']} [{$row['durability']}/{$row['durmax']}] ({$ap}AP)"
				);
		}
		return $res;
	}
	
	# show skill?
	function show(&$actor)
	{
		$this->ci->load->model('map');
		return $this->ci->map->cellHasClass('forge', $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors'], $cellinfo);
	}
}
