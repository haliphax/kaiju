<?php if(! defined('BASEPATH')) exit();

class faction extends Model
{
	function faction()
	{
		parent::Model();
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
