<?php if(! defined('BASEPATH')) exit();

class e_npc_chancetohit75 extends NoCacheModel
{
	private $ci;
	
	function e_npc_chancetohit75()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}
	
	function chancetohit()
	{
		return 15;
	}
}
