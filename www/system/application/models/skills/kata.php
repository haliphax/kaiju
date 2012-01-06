<?php if(! defined('BASEPATH')) exit();

class kata extends Model
{
	private $ci;
	
	# constructor
	function kata()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
	}

	# use skill
	function fire(&$actor, $args)
	{
		$kata = $args[0];
		
		if($kata == 0)
		{
			$this->kata_remove($actor['actor']);
			return array("You are no longer assuming any stance.");
		}
		
		$r = $this->ci->skills->getInfo($kata);
		$which = $r['abbrev'];
		$this->ci->load->model('skills/' . $which);
		return call_user_func(array($this->ci->$which, 'fire'), $actor);
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
			actor = ? and abbrev like 'kata_%'
SQL;
		$q = $this->db->query($s, array($actor['actor']));
		$r = $q->result_array();
		$ret = array(array(0, "None"));
		foreach($r as $row)
			$ret[] = array($row['skill'],
				"{$row['sname']} ({$row['cost_mp']}MP)");
		return $ret;
	}
	
	# remove kata
	function kata_remove($actor)
	{
		$s = <<<SQL
			delete from actor_effect where effect in
			(select effect from effect where abbrev like 'kata_%')
			and actor = ?
SQL;
		$this->db->query($s, array($actor));
	}
}
