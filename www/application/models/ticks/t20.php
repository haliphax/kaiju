<?php if(! defined('BASEPATH')) exit();

class t20 extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function fire()
	{
		# AP regeneration
		$sql = <<<SQL
			update actor set
				stat_hp = (case
					when stat_hpmax = 1000 then stat_hpmax
					else stat_hp
				end),
				stat_ap = (case
					when stat_apmax = 1000 then stat_apmax
					when stat_ap >= stat_apmax then stat_ap
					else least(stat_apmax, stat_ap + 2)
				end),
				stat_mp = (case
					when stat_mpmax = 1000 then stat_mpmax
					when stat_mp >= stat_mpmax then stat_mp
					else least(stat_mpmax, stat_mp + 1)
				end),
				evts = 1
SQL;
		$this->db->query($sql);
		
		# perishable items counter
		$sql = <<<SQL
			update actor_item set lifespan = lifespan - 1
			where lifespan is not null
SQL;
		$this->db->query($sql);
		$sql = <<<SQL
			select actor, ai.inum, eq_slot, iname from actor_item ai
			join item i on ai.inum = i.inum
			where ai.lifespan <= 0
SQL;
		$q = $this->db->query($sql);
		$r = $q->result_array();
		$who = array();
		
		foreach($r as $row)
		{
			if($who['actor'] != $row['actor'])
				$who = $this->actor->getInfo($row['actor']);
			if($row['eq_slot'])
				$this->actor->removeItems($row['inum'], $who);
			$this->actor->sendEvent("Your {$row['iname']} perished.",
				$row['actor']);
		}
		
		$sql = 'delete from actor_item where lifespan <= 0';
		$this->db->query($sql);
		
		# clear NPC bodies
		$s = <<<SQL
			update actor
			set map = 0 - map, last = UNIX_TIMESTAMP()
			where user < 0 and map > 0 and stat_hp <= 0
SQL;
		$this->db->query($s);
	
		# NPC spawning
		$q = $this->db->query('select abbrev from npc');
		$r = $q->result_array();
		
		foreach($r as $row)
		{
			$which = "n_{$row['abbrev']}";
			$this->load->model("npcs/{$which}");
			$this->$which->spawn();
		}
	}
}
