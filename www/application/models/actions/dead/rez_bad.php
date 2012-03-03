<?php if(! defined('BASEPATH')) exit();

class rez_bad extends NoCacheModel
{
	private $ci;
	
	function rez_bad()
	{
		parent::__construct();
		#$this->ci =& get_instance();
	}
	
	function show()
	{
		return false;
	}	
}