<?php if(! defined('BASEPATH')) exit();

class rez_graveyard extends CI_Model
{
	private $ci;
	
	function rez_graveyard()
	{
		parent::__construct();
		#$this->ci =& get_instance();
	}
	
	function show()
	{
		return false;
	}
}