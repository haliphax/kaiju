<?php if(! defined('BASEPATH')) exit();

class faction extends CI_Model
{
	function faction()
	{
		parent::__construct();
		$this->load->database();
	}
	
	function getFactions()
	{
		$q = $this->db->query(
			'select faction, descr from faction');
		return $q->result_array();
	}

	function getInfo($f)
	{
		$q = $this->db->query('select * from faction where faction = ?', $f);
		return $q->row_array();
	}
}
