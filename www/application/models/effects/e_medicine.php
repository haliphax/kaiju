<?php if(! defined('BASEPATH')) exit();

class e_medicine extends NoCacheModel
{
	function e_medicine()
	{
		parent::__construct();
	}
	
	function heal(&$victim, &$actor, &$heal)
	{
		$heal['hp'] = round($heal['hp'] * 1.1);
		return array('Your healing powers were magnified.');
	}
}