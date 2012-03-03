<?php if(! defined('BASEPATH')) exit();

class c_forest extends NoCacheModel
{
	private $ci;
	
	function c_forest()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}

	function arrive(&$actor)
	{
		$msg = array();
		
		if($this->ci->actor->hasEffect('climbing', $actor['actor']))
		{
			$msg[] =
				"You sail effortlessly between trees through the forest.";
		}
		
		$ret = $this->ci->actor->spendAP(1, &$actor);
		foreach($ret as $r) $msg[] = $r;
		return array(true, $msg);
	}
}