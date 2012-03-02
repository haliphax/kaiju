<?php if(! defined('BASEPATH')) exit();

class sing extends CI_Model
{
	private $ci;
	
	# constructor
	function sing()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
	}

	# use skill
	function fire(&$actor, $args)
	{
		$song = $args[0];
		
		if($song == 0)
		{
			if($this->ci->actor->hasEffectLike('song_%', $actor['actor']))
			{
				$this->ci->load->model('map');
				$this->ci->map->sendCellEvent(
					"{$actor['aname']} stops singing.", array($actor['actor']),
					$actor['map'], $actor['x'], $actor['y'], $actor['indoors']);
				$this->song_remove($actor['actor']);		
				return array("You are no longer singing.");
			}
			
			return;
		}
		
		$this->song_remove($actor['actor']);
		$r = $this->ci->skills->getInfo($song);
		$which = $r['abbrev'];
		$cost = $this->ci->skills->getCost($which);
		if($cost['cost_ap'] > $actor['stat_ap'])
			return $this->ci->skills->noap;
		if($cost['cost_mp'] > $actor['stat_mp'])
			return $this->ci->skills->nomp;
		$msg = array();
		$ret = $this->ci->actor->spendAP($cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->spendMP($cost['cost_mp'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->addEffect($which, $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function show()
	{
		return true;
	}
	
	function params(&$actor)
	{
		$s = <<<SQL
			select ak.skill, sname, cost_mp from actor_skill ak
			join skill s on ak.skill = s.skill where
			actor = ? and abbrev like 'song_%'
SQL;
		$q = $this->db->query($s, $actor);
		$r = $q->result_array();
		$ret = array(array(0, "None"));
		foreach($r as $row)
			$ret[] = array($row['skill'],
				"{$row['sname']} ({$row['cost_mp']}MP)");
		return $ret;
	}
	
	# remove sing
	function song_remove($actor)
	{
		$s = <<<SQL
			delete from actor_effect where effect in
			(select effect from effect where abbrev like 'song_%')
			and actor = ?
SQL;
		$this->db->query($s, array($actor));
	}
}
