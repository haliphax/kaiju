<?php if(! defined('BASEPATH')) exit();

class c_lavahot extends NoCacheModel
{
	private $ci;
	
	function c_lavahot()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}

	function arrive(&$actor)
	{
		$msg = array();
		$ret = $this->ci->actor->spendAP(1, &$actor);
		foreach($ret as $r) $msg[] = $r;
		$ret = $this->ci->actor->damage(5, &$actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = 'OH, GOD! THE PAIN! Your flesh is being seared!';
		return array(true, $msg);
	}
}