<?php if(! defined('BASEPATH')) exit();

class user extends CI_Model
{
	private $ci;

	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
	}

	function create($username, $password, $email)
	{
		$this->db->query(
			'insert into user (uname, pass, email, created) values (?, md5(?), ?, ?)',
			array($username, $this->config->item('salt') . $password, $email, time()));
		if($this->db->affected_rows() <= 0)
			return false;
		return $this->db->insert_id();
	}

	function checkEmail($user, $email)
	{
		$q = $this->db->query(
			'select email from user where lower(uname) = lower(?)',
			$user);
		if($q->num_rows() <= 0)
			return false;
		$r = $q->row_array();
		if($r['email'] !== $email)
			return false;
		return true;
	}

	function getResetToken($user)
	{
		$q = $this->db->query(
			'select user from user where lower(uname) = lower(?)',
			$user);
		if($q->num_rows() <= 0)
			return false;
		$r = $q->row_array();
		$user = $r['user'];
		$this->db->query(
			'delete from password_reset where user = ?',
			$user);
		$now = time();
		$token = md5($this->config->item('salt') . $user . $now);
		$this->db->query(
			'insert into password_reset (user, token, stamp) values (?, ?, ?)',
			array($user, $token, $now));
		return $token;
	}

	function checkResetToken($token)
	{
		$q = $this->db->query(
			'select user from password_reset where token = ?',
			$token);
		if($q->num_rows() <= 0)
			return false;
		$r = $q->row_array();
		return $r['user'];
	}

	function setPassword($user, $password)
	{
		$this->db->query(
			'update user set pass = ? where user = ?',
			array(md5($this->config->item('salt') . $password), $user));
		return ($this->db->affected_rows() > 0);
	}

	# verify login =============================================================
	function checkLogin($name, $pass)
	{
		$sql = <<<SQL
			select user from user
			where lower(uname) = lower(?) and pass = md5(?)
SQL;
		$query = $this->db->query($sql, array($name, $this->config->item('salt') . $pass));
		if($query->num_rows() <= 0)
			return false;
		$result = $query->row_array();
		return $result['user'];
	}

	# get user information =====================================================
	function getInfo($user)
	{
		$sql = 'select * from user where user = ?';
		$query = $this->db->query($sql, array($user));
		return $query->row_array();
	}
	
	# is user moderator? =======================================================
	function isMod($user)
	{
		$sql = 'select 1 from user_mod where user = ? limit 1';
		$q = $this->db->query($sql, array($user));
		if($q->num_rows() <= 0) return false;
		return true;	
	}
	
	# can user edit given map? =================================================
	function canEditMap($user, $map)
	{
		$sql = <<<SQL
			select 1 from user_priv_mapedit
			where user = ? and (map = ? or map = 0)
			limit 1
SQL;
		$q = $this->db->query($sql, array($user, $map));
		if($q->num_rows() <= 0) return false;
		return true;
	}

	# get characters for given user ============================================
	function getActors($user)
	{
		$sql = <<<SQL
			select a.*, a.faction, f.descr as faction_name, clan from actor a
			join faction f on a.faction = f.faction
			left join clan_actor ca on a.actor = ca.actor
			where user = ? order by actor asc
SQL;
		$query = $this->db->query($sql, array($user));
		return $query->result_array();
	}
	
	# get number of characters =================================================
	function getNumActors($user)
	{
		$sql = 'select count(1) as cnt from actor where user = ?';
		$query = $this->db->query($sql, array($user));
		$res = $query->row_array();
		return $res['cnt'];
	}
	
	# update user's email ======================================================
	function setEmail($email, $user)
	{
		$sql = 'select 1 from user where email = ? and user != ?';
		$query = $this->db->query($sql, array($email, $user));
		if($query->num_rows() > 0) return false;
		$sql = 'update user set email = ? where user = ?';
		$this->db->query($sql, array($email, $user));
		return true;
	}
	
	# set last login timestamp =================================================
	function setLast($user)
	{
		$sql = 'update user set last = unix_timestamp() where user = ?';
		$this->db->query($sql, array($user));
		if($this->db->affected_rows() <= 0) return false;
		return true;
	}
}
