<?php if(! defined('BASEPATH')) exit();

class c_hills extends CI_Model
{
	private $ci;
	
	function c_hills()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}

	function arrive(&$actor)
	{
		$msg = array();
		$ret = $this->ci->actor->spendAP(2, &$actor);
		foreach($ret as $r) $msg[] = $r;
		return array(true, $msg);
	}
}