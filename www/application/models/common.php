<?php if(! defined('BASEPATH')) exit();

# common data model ============================================================

class common extends NoCacheModel
{
	public $cellinfo;	# models/map->getCellInfo cached results
	
	function __construct()
	{
		parent::__construct();
		$this->cellinfo = false;
	}
}