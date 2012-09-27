<?php if(! defined('BASEPATH')) exit();

class e_quickness extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}

	function attack(&$vic, &$actor, &$swing)
	{
		if(rand(1, 20) > 3) return;
		$this->ci->actor->incStat('ap', 1, $actor['actor']);
		return array(
			"You strike with quickness and fluidity, expending no energy whatsoever."
			);
	}
}