<?php if(! defined('BASEPATH')) exit();

class kujikiri extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function kujikiri()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('kujikiri');
	}

	# use skill
	function fire(&$actor, $args)
	{
		$kujiin = $args[0];
		$msg = array();
		
		if($kujiin == 0)
		{
			$this->kujikiri_remove($actor['actor']);
			$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
			foreach($ret as $r) $msg[] = $r;
			$msg[] = "You are no longer maintaining any kuji-in.";
			return $msg;
		}
		
		$r = $this->ci->skills->getInfo($kujiin);
		$which = $r['abbrev'];
		$this->ci->load->model('skills/' . $which);
		return call_user_func(array($this->ci->$which, 'fire'), $actor);
	}
	
	# skill parameters
	function params($actor)
	{
		$s = <<<SQL
			select ak.skill, sname, cost_mp from actor_skill ak
			join skill s on ak.skill = s.skill where
			actor = ? and abbrev like 'kujiin_%'
SQL;
		$q = $this->db->query($s, array($actor['actor']));
		$r = $q->result_array();
		$ret = array(array(0, "None"));
		foreach($r as $row)
			$ret[] = array($row['skill'],
				"{$row['sname']} ({$row['cost_mp']}MP)");
		return $ret;
	}
	
	# show skill?
	function show()
	{
		return true;
	}
	
	# purchase skill
	function purchase(&$actor)
	{
		$this->ci->actor->addEffect('kujikiri', &$actor);
	}

	# remove kuji-in
	function kujikiri_remove($actor)
	{
		$s = <<<SQL
			delete from actor_effect where effect in
			(select effect from effect where abbrev like 'kujiin_%')
			and actor = ?
SQL;
		$this->db->query($s, array($actor));
	}
}
