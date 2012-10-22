<?php if(! defined('BASEPATH')) exit();

class n_oni extends NPCModel
{
	function __construct()
	{
		parent::__construct();
		$this->ci->load->model('map');
	}
	
	function spawn()
	{
		parent::spawn("{0} appears from beyond the void.");
	}
	
	function tick($tick)
	{
	}
	
	function defend(&$victim, &$actor, &$swing)
	{
	}
}
