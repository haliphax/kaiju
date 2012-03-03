<?php if(! defined('BASEPATH')) exit();

class rez_bad extends Model
{
	private $ci;
	
	function rez_bad()
	{
		parent::Model();
		#$this->ci =& get_instance();
	}
	
	function show()
	{
		return false;
	}	
}