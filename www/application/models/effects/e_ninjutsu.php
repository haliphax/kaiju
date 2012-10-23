<?php if(! defined('BASEPATH')) exit();

class e_ninjutsu extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}

	function attack(&$vic, &$actor, &$swing)
	{
		if($swing['wep']['distance'] == 'ranged'
			|| $swing['wep']['eq_type'] != '1H') return;
		$swing['crit'] += 2;
	}
}