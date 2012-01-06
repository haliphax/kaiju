<?php if(! defined('BASEPATH')) exit();

class rez_graveyard extends Model
{
	private $ci;
	
	function rez_graveyard()
	{
		parent::Model();
		#$this->ci =& get_instance();
	}
	
	function show()
	{
		return false;
	}
}