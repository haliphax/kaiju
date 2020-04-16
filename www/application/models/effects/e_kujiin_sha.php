<?php if(! defined('BASEPATH')) exit();

class e_kujiin_sha extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}

	function attack(&$vic, &$actor, &$swing)
	{
		$this->ci->load->model('effects/e_kujikiri');
		$this->ci->e_kujikiri->_attack($actor);
	}
	
	function hit(&$victim, &$actor, &$hit)
	{
		if($actor['stat_hp'] >= $actor['stat_hpmax']) return;
		$this->ci->actor->incStat('hp', 1, $actor['actor']);
	}
}
