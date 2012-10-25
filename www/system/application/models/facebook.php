<?php if(! defined('BASEPATH')) exit();

class Facebook extends Model
{
	function Facebook()
	{
		parent::Model();
		$this->load->database();
	}

	function getUser()
	{
		$q = $this->db->query("select user from user where fb = ?", $this->session->userdata('fb_user'));

		if($q->num_rows() > 0)
		{
			$r = $q->row_array();
			return $r['user'];
		}

		return false;
	}

	function registerNewUser()
	{
		$this->db->query("insert into user (fb, created) values (?, UNIX_TIMESTAMP())", $this->session->userdata('fb_user'));
		if($this->db->affected_rows() <= 0)
			return false;
		$id = $this->db->insert_id();
		$this->session->set_userdata('user', $id);
		return true;
	}

	function linkUser($uid)
	{
		$this->db->query('update user set fb = ? where user = ?', array($this->session->userdata('fb_user'), $uid));
		return ($this->db->affected_rows() >= 0);
	}
}
