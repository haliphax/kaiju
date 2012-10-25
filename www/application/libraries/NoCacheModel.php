<?php if(! defined('BASEPATH')) exit();

class NoCacheModel extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->db->save_queries = false;
	}
}

