<?php if(! defined('BASEPATH')) exit();

class tdata extends Model
{
	private $store;
	
	function __construct()
	{
		parent::Model();
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
