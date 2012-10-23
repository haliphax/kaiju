<?php if(! defined('BASEPATH')) exit();

class c_mountains extends Model
{
	private $ci;
	
	function c_mountains()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function arrive(&$actor)
	{
		$msg = array();		
		$ret = $this->ci->actor->spendAP(5, &$actor);
		foreach($ret as $r) $msg[] = $r;
		return array(true, $msg);	
	}
}