<?php if(! defined('BASEPATH')) exit();

class e_ninjutsu extends NoCacheModel
{
	private $ci;
	
	function e_ninjutsu()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}

	function attack(&$vic, &$actor, &$swing)
	{
		if($swing['wep']['distance'] == 'ranged'
			|| $swing['wep']['eq_type'] != '1H') return;
		$swing['crit'] += 2;
	}
}