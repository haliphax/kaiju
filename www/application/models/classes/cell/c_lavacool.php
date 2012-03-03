<?php if(! defined('BASEPATH')) exit();

class c_lavacool extends NoCacheModel
{
	function c_lavacool()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function arrive(&$actor)
	{
		$msg = array();
		$ret = $this->ci->actor->spendAP(5, &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->damage(1, &$actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = 'It burns! Ouch!';
		return array(true, $msg);
	}
}