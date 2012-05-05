<?php if(! defined('BASEPATH')) exit();

class st_stronghold extends CI_Model
{
	private $ci;
	
	function st_stronghold()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}
	
	function surr(&$actor)
	{
		return "This place feels safe.";
	}
	
	function b_enter(&$actor)
	{
		return array(true, array("You enter the stronghold."));
	}
	
	function b_exit(&$actor)
	{
		return array(true, array("You leave the stronghold."));
	}
	
	function arrive(&$actor)
	{
		return array(true, array("You have arrived."));
	}
	
	function leave(&$actor)
	{
		return array(true, array("You have left."));
	}
}
