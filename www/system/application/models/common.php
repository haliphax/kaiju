<?php if(! defined('BASEPATH')) exit();

# common data model ============================================================

class common extends Model
{
	public $cellinfo;	# models/map->getCellInfo cached results
	
	function __construct()
	{
		parent::Model();
		$this->cellinfo = false;
	}
}