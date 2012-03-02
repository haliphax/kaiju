<?php if(! defined('BASEPATH')) exit();

class clan extends CI_Model
{
	private $ci;

	# constructor ==============================================================
	function clan()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
	}

	# remove member from clan ==================================================
	function removeMember($clan, $actor)
	{
		$this->db->query('delete from clan_actor where clan = ? and actor = ?',
			array($clan, $actor));
		return ($this->db->affected_rows() > 0);
	}
	
	# get list of clans ========================================================
	function getClanList()
	{
		$q = $this->db->query('select * from clan');
		return $q->result_array();
	}
	
	# get clan information =====================================================
	function getInfo($clan)
	{
		$s = <<<SQL
			select c.*, cs.map, cs.building, cs.shield, mc.x, mc.y,
				count(actor) as members from clan c
			left join clan_actor ca on c.clan = ca.clan
			left join clan_stronghold cs on c.clan = cs.clan
			left join map_cell mc on cs.map = mc.map 
				and cs.building = mc.building
			where c.clan = ?
			group by c.clan
SQL;
		$q = $this->db->query($s, $clan);
		$ret = $q->row_array();
		$s = <<<SQL
			select a.actor, aname from clan_actor ca
			join actor a on ca.actor = a.actor
			where clan = ? and rank = 0
SQL;
		$q = $this->db->query($s, $clan);
		$r = $q->row_array();
		$ret['leader'] = $r['actor'];
		$ret['leader_name'] = $r['aname'];
		return $ret;
	}
	
	# get stronghold shield value ==============================================
	function getStrongholdShield($clan)
	{
		$s = 'select shield from clan_stronghold where clan = ?';
		$q = $this->db->query($s, $clan);
		if($q->num_rows() <= 0) return false;
		$r = $q->row_array();
		return $r['shield'];
	}
	
	# boost the stronghold shield ==============================================
	function incStrongholdShield($clan, $val)
	{
		$v = $this->getStrongholdShield($clan);
		if($v >= 100) return false;
		$s = 'update clan_stronghold set shield = shield + ? where clan = ?';
		$this->db->query($s, array($val, $clan));
		if($this->db->affected_rows() > 0) return true;
		return false;
	}
	
	# damage the stronghold shield =============================================
	function decStrongholdShield($clan, $val)
	{
		$v = $this->getStrongholdShield($clan);
		if($v <= 0) return false;
		$s = 'update clan_stronghold set shield = shield - ? where clan = ?';
		$this->db->query($s, array(min($v, $val), $clan));
		if($this->db->affected_rows() <= 0) return false;
		
		# shield destroyed
		if($val >= $v)
		{
			$claninfo = $this->getInfo($clan);
			$this->ci->load->model('map');
			$this->ci->map->sendCellEvent(
				"The barrier protecting this stronghold has collapsed!",
				false, $claninfo['map'], $claninfo['x'], $claninfo['y'], 0);
			$this->ci->map->sendCellEvent(
				"The barrier protecting this stronghold has collapsed!",
				false, $claninfo['map'], $claninfo['x'], $claninfo['y'], 1);
		}
		
		return true;
	}

	# get clan roster ==========================================================
	function getRoster($clan)
	{
		$s = <<<SQL
			select a.* from clan_actor ca
			join actor a on ca.actor = a.actor
			where ca.clan = ?
SQL;
		$q = $this->db->query($s, $clan);
		return $q->result_array();
	}
	
	# set clan's options =======================================================
	function setOptions($clan, $opts)
	{
		$params = array();
		$s = 'update clan set ';
		
		foreach($opts as $k => $o)
		{
			$s .= "{$k} = ?, ";
			$params[] = $o;
		}
		
		$s = substr($s, 0, strlen($s) - 2);
		$s .= ' where clan = ?';
		$params[] = $clan;
		$this->db->query($s, $params);
		return ($this->db->affected_rows() > 0);
	}
	
	# submit application for clan membership ===================================
	function submitApplication($clan, $who, $msg)
	{
		$s = <<<SQL
			insert into clan_application (clan, actor, msg, stamp)
			values (?, ?, ?, UNIX_TIMESTAMP())
SQL;
		$this->db->query($s, array($clan, $who, htmlentities($msg)));
		return ($this->db->affected_rows() > 0);
	}
	
	# get currently active applications for clan membership ====================
	function getApplications($clan)
	{
		$s = <<<SQL
			select aname, ca.actor, msg from clan_application ca
			join actor a on ca.actor = a.actor
			where clan = ?
SQL;
		$q = $this->db->query($s, $clan);
		return $q->result_array();
	}
	
	# get outgoing invitations for clan membership =============================
	function getInvitations($clan)
	{
		$s = <<<SQL
			select aname, ci.actor, msg from clan_invitation ci
			join actor a on ci.actor = a.actor
			where clan = ?
SQL;
		$q = $this->db->query($s, $clan);
		return $q->result_array();	
	}
	
	# deny application for clan membership =====================================
	function denyApplication($clan, $actor)
	{
		$this->db->query(
			'delete from clan_application where clan = ? and actor = ?',
			array($clan, $actor));
		return ($this->db->affected_rows() > 0);
	}
	
	# accept application for clan membership ===================================
	function acceptApplication($clan, $actor)
	{
		$s = 'delete from clan_application where clan = ? and actor = ?';
		$this->db->query($s, array($clan, $actor));
		if($this->db->affected_rows() <= 0) return false;
		$s = 'insert into clan_actor (clan, actor) values (?, ?)';
		$this->db->query($s, array($clan, $actor));
		if($this->db->affected_rows() <= 0) return false;
		$this->ci->load->model('actor');
		$claninfo = $this->getInfo($clan);
		$this->ci->actor->sendEvent(
			"<b>{$claninfo['descr']} accepted your application for membership!</b>",
			$actor
			);
		return true;
	}
	
	# cancel outgoing invitation for clan membership ===========================
	function cancelInvitation($clan, $actor)
	{
		$s = 'delete from clan_invitation where clan = ? and actor = ?';
		$this->db->query($s, array($clan, $actor));
		return ($this->db->affected_rows() > 0);
	}
	
	# send invitation for clan membership ======================================
	function sendInvitation($clan, $actor, $msg)
	{
		$s = <<<SQL
			insert into clan_invitation (clan, actor, stamp, msg)
			values (?, ?, UNIX_TIMESTAMP(), ?)
SQL;
		$this->db->query($s, array($clan, $actor, $msg));
		if($this->db->affected_rows() <= 0) return false;
		$clan = $this->getInfo($clan);
		$this->ci->actor->sendEvent(
			"<b>{$clan['descr']}</b> sent you a membership invitation.</b>",
			$actor);
		return true;
	}
	
	# set clan's stronghold location ===========================================
	function setStronghold($clan, $map, $bldg)
	{
		$s = <<<SQL
			insert into clan_stronghold (clan, map, building) values (?, ?, ?)
SQL;
		$this->db->query($s, array($clan, $map, $bldg));
		return ($this->db->affected_rows() > 0);
	}
	
	# clear clan's stronghold data =============================================
	function clearStronghold($clan)
	{
		$this->db->query('delete from clan_stronghold where clan = ?', $clan);
		return ($this->db->affected_rows() > 0);
	}
	
	# get current clan relations ===============================================
	function getRelations($clan)
	{
		$s = <<<SQL
			select rclan, standing, descr from clan_relation cr
			join clan c on cr.rclan = c.clan
			where cr.clan = ?
SQL;
		$q = $this->db->query($s, $clan);
		return $q->result_array();
	}
	
	# check for relation with clan =============================================
	function existsRelation($clana, $clanb)
	{
		$q = $this->db->query(
			'select 1 from clan_relation where clan = ? and rclan = ?',
			array($clana, $clanb, $clanb, $clana)
			);
		return ($q->num_rows() > 0);
	}
	
	# remove clan relation =====================================================
	function removeRelation($clan, $rclan)
	{
		$s = <<<SQL
			delete from clan_relation
			where clan = ? and rclan = ?
SQL;
		$this->db->query($s, array($clan, $rclan));
		return ($this->db->affected_rows() > 0);
	}
	
	# add new clan relation ====================================================
	function addRelation($clan, $rclan)
	{
		$claninfo = $this->getInfo($clan);
		$rclaninfo = $this->getInfo($rclan);
		$standing = 'Enemy';
		if($claninfo['faction'] == $rclaninfo['faction']) $standing = 'Ally';
		$s = <<<SQL
			select count(1) as cnt from clan_relation
			where clan = ? and standing = ?
SQL;
		$q = $this->db->query($s, array($clan, $standing));
		$r = $q->row_array();
		if($r['cnt'] >= 5) return false;
		$this->db->query(
			'insert into clan_relation (clan, rclan, standing) values(?, ?, ?)',
			array($clan, $rclan, $standing)
			);
		$id = $this->db->insert_id();
		return is_int($id);
	}
	
	# has clan's standard been captured in the last 24h? =======================
	function capturedRecently($rclan, $clan = false)
	{
		$s = <<<SQL
			select 1 from clan_capture
			where rclan = ? and stamp >= ? and reclaimed = b'0'
SQL;
		$params = array($rclan, time() - 86400);
		
		if($clan !== false)
		{
			$s .= ' and clan = ?';
			$params[] = $clan;
		}
		
		$q = $this->db->query($s, $params);
		if($q->num_rows() <= 0) return false;
		return true;
	}
	
	# replace clan leader ======================================================
	function replaceLeader($clan, $successor)
	{
		$this->db->query(
			'update clan_actor set rank = 1 where rank = 0 and clan = ?',
			$clan);
		if($this->db->affected_rows() <= 0) return false;
		$this->db->query(
			'update clan_actor set rank = 0 where actor = ? and clan = ?',
			array($successor, $clan));
		return ($this->db->affected_rows() > 0);
	}
	
	# disband clan =============================================================
	function disband($clan)
	{
		$sql[] = 'delete from clan where clan = ?';
		$sql[] = 'delete from clan_actor where clan = ?';
		$sql[] = 'delete from clan_relation where clan = ?';
		$sql[] = 'delete from clan_relation where rclan = ?';
		$sql[] = 'delete from clan_application where clan = ?';
		$sql[] = 'delete from clan_invitation where clan = ?';
		$sql[] = 'delete from clan_stronghold where clan = ?';
		foreach($sql as $s)
			$this->db->query($s, $clan);
	}
	
	# send event to clan members ===============================================
	function sendEvent($event, $clan, $excludes = false)
	{
		$this->ci->load->model('actor');
		$members = $this->getRoster($clan);
		$recipients = array();
		foreach($members as $member)
			if($excludes == false || ! in_array($members['actor'], $excludes))
				$recipients[] = $member['actor'];
		$this->ci->actor->sendEvent($event, $recipients);
	}
	
	function isAllyOf($clan, $rclan)
	{
		$myclan = $this->getInfo($clan);
		$theirclan = $this->getInfo($rclan);
		if($myclan['faction'] != $theirclan['faction'])
			return false;
		if(! $this->existsRelation($clan, $rclan))
			return false;
		return true;
	}	
	
	function isEnemyOf($clan, $rclan)
	{
		$myclan = $this->getInfo($clan);
		$theirclan = $this->getInfo($rclan);
		if($myclan['faction'] == $theirclan['faction'])
			return false;
		if(! $this->existsRelation($clan, $rclan))
			return false;
		return true;	
	}
}
