<?php if(! defined('BASEPATH')) exit();

class e_combat extends NoCacheModel
{
	private $ci;
	
	function e_combat()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}
	
	function chancetohit()
	{
		return 5;
	}
}
