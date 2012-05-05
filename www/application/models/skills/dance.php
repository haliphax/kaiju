<?php if(! defined('BASEPATH')) exit();

class dance extends CI_Model
{
	private $ci;
	
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
	}

	# use skill
	function fire(&$actor, $args)
	{
		$dance = $args[0];
		
		if($dance == 0)
		{
			if($this->ci->actor->hasEffectLike('dance_%', $actor['actor']))
			{
				$this->ci->load->model('map');
				$this->ci->map->sendCellEvent(
					"{$actor['aname']} stops dancing.", array($actor['actor']),
					$actor['map'], $actor['x'], $actor['y'], $actor['indoors']);
				$this->dance_remove($actor['actor']);		
				return array("You are no longer dancing.");
			}
			
			return;
		}
		
		$this->dance_remove($actor['actor']);		
		$r = $this->ci->skills->getInfo($dance);
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
			actor = ? and abbrev like 'dance_%'
SQL;
		$q = $this->db->query($s, $actor['actor']);
		$r = $q->result_array();
		$ret = array(array(0, "None"));
		foreach($r as $row)
			$ret[] = array($row['skill'],
				"{$row['sname']} ({$row['cost_mp']}MP)");
		return $ret;
	}
	
	# remove dance
	function dance_remove($actor)
	{
		$s = <<<SQL
			delete from actor_effect where effect in
			(select effect from effect where abbrev like 'dance_%')
			and actor = ?
SQL;
		$this->db->query($s, array($actor));
	}
}
