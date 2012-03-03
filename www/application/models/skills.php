<?php if(! defined('BASEPATH')) exit();

class skills extends NoCacheModel
{
	private $ci;
	public $nomp = array("You do not have enough mystical energy.");
	public $noap = array("You lack the strength.");
	public $bodyparts = array(
		array('Head', 'Head'),
		array('Torso', 'Torso'),
		array('Arms', 'Arms'),
		array('Legs', 'Legs')
		);
	
	function skills()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}

#===============================================================================
# re-usable functions
#===============================================================================

	# too far away? ============================================================
	function canMelee($v, $a, $d)
	{
		if($this->ci->actor->isElevated($a) != $this->ci->actor->isElevated($v)
			&& $d == 'melee')
		{
			return false;
		}
		
		return true;
	}
	
#===============================================================================
# skill-related methods
#===============================================================================
	
	# get skill tree ===========================================================
	
	function getTree($aclass, $actor)
	{
		$sql = <<<SQL
			select distinct st1.skill, st1.xp, (
				case when st1.skill < 0
				then (select abbrev from effect where effect = 0 - st1.skill)
				else (select abbrev from skill where skill = st1.skill)
				end
				) as abbrev, (
				case when st1.parent < 0
				then (select abbrev from effect where effect = 0 - st1.parent)
				else (select abbrev from skill where skill = st1.parent)
				end
				) as pabbrev, (
				case when st1.skill < 0
				then (select ename from effect where effect = 0 - st1.skill)
				else (select sname from skill where skill = st1.skill)
				end
				) as sname, (
				case when st1.skill in
					(select parent from skill_tree where parent = st1.skill)
				then 1
				else 0
				end
				) as kids, (
				case when st1.skill in
					(select skill from actor_skill where actor = ?)
					or 0 - st1.skill in
					(select effect from actor_effect where actor = ?)
				then 1
				else 0
				end
				) as got
			from skill_tree st1
			left join skill_tree st2 on st1.skill = st2.parent
			where st1.class = ?
			order by pabbrev, lower(sname) asc
SQL;
		$query = $this->db->query($sql, array($actor, $actor, $aclass));
		$skills = $query->result_array();
		$ret = array();
		foreach($skills as $s)
			if(! $s['pabbrev'])
				$ret[] = $this->getTree_helper($s, $skills);
		return $ret;
	}
	
	function getTree_helper(&$skill, &$skills)
	{
		$data = array(
			'skill' => $skill['skill'],
			'sname' => $skill['sname'],
			'got' => $skill['got'],
			'xp' => $skill['xp']
			);
		foreach($skills as $s)
			if($s['pabbrev'] == $skill['abbrev'])
				$data['kids'][] = $this->getTree_helper($s, $skills);
		return $data;
	}
	
	# purchase =================================================================
	function purchase($aclass, $skill, &$actor)
	{
		if(! $this->ci->actor->hasClass($aclass, $actor['actor']))
		{
			echo "Fail";
			return false;
		}
		
		$sql = <<<SQL
			select * from skill_tree
			where (faction = ? or faction = 0) and class = ? and skill = ?
SQL;
		$query = $this->db->query($sql, array($actor['faction'], $aclass,
			$skill));
		if($query->num_rows() <= 0) return false;
		$res = $query->row_array();
		if($res['xp'] > $actor['stat_xp']) return false;
		
		if($res['parent'] != 0)
			if($res['parent'] < 0
				&& ! $this->ci->actor->hasEffect(0 - $res['parent'],
					$actor['actor']))
			{
				return false;
			}
			else if($res['parent'] > 0
				&& ! $this->ci->actor->hasSkill($res['parent'],
					$actor['actor']))
			{
				return false;
			}
		
		$sql = 0;		
		if($skill < 0)
			$sql = 'insert into actor_effect (actor, effect) values (?, 0 - ?)';
		else
			$sql = 'insert into actor_skill (actor, skill) values (?, ?)';
		$this->db->query($sql, array($actor['actor'], $skill));
		if($this->db->affected_rows() <= 0) return false;
		$this->ci->actor->incStat('xp', 0 - $res['xp'], $actor['actor']);
		$this->ci->actor->incStat('xpspent', $res['xp'], $actor['actor']);
		if($skill < 0)
			$sql = <<<SQL
				select abbrev from effect e
					join skill_effect se on e.effect = se.effect
					where e.effect = 0 - ? and purchase = b'1'
SQL;
		else
			$sql = 
				"select abbrev from skill where skill = ? and purchase = b'1'";
		$query = $this->db->query($sql, array($skill));
		if($query->num_rows() == 0) return true;
		$res = $query->row_array();
		$which = $res['abbrev'];		
		$this->ci->load->model("skills/{$which}");
		call_user_func(array($this->ci->$which, 'purchase'), $actor);
		return true;
	}
	
	# determine AP cost ========================================================
	function getCost($skill)
	{
		if(is_numeric($skill))
			$sql = 'select cost_ap, cost_mp from skill where skill = ?';
		else
			$sql = <<<SQL
				select cost_ap, cost_mp from skill
				where lower(abbrev) = lower(?)
SQL;
		$query = $this->db->query($sql, array($skill));
		if($query->num_rows() <= 0) return false;
		return $query->row_array();
	}
	
	# get skill info ===========================================================
	function getInfo($skill)
	{
		if($skill < 0)
			$sql = <<<SQL
				select ename as sname, abbrev, descr from effect
				where effect = 0 - ?
SQL;
		else
			$sql = 
				'select sname, abbrev, descr from skill where skill = ?';
		$query = $this->db->query($sql, array($skill));
		return $query->row_array();
	}
	
	# get a skill's parameters =================================================
	function getParameters($skill, &$actor)
	{
		$sql = 'select abbrev from skill where skill = ?';
		$query = $this->db->query($sql, array($skill));
		$res = $query->row_array();
		$which = $res['abbrev'];
		$this->ci->load->model('skills/' . $which);
		return call_user_func(array($this->ci->$which, 'params'), $actor);
	}
	
	# is skill repeatable? =====================================================
	function isRepeatable($skill)
	{
		$s = <<<SQL
			select 1 from skill
			where skill = ? and repeatable = b'1'
SQL;
		$q = $this->db->query($s, array($skill));
		return ($q->num_rows() > 0);
	}
}
