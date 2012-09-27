<?php if(! defined('BASEPATH')) exit();

class e_defense_ranged extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}

	function defend(&$vic, &$actor, &$swing)
	{
		if($swing['wep']['distance'] != 'melee')
			$swing['chance']--;
	}
}