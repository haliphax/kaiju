<?php if(! defined('BASEPATH')) exit();

class tdata extends NoCacheModel
{
	private $store;
	
	function __construct()
	{
		parent::__construct();
		$this->store = array();
	}
	
	function set($key, $val)
	{
		$this->store[$key] = $val;
	}
	
	function get($key, $val)
	{
		return $this->store[$key];
	}
}
