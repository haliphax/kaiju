<?php if(! defined('BASEPATH')) exit();

class e_kujiin_rin extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function attack(&$vic, &$actor, &$swing)
	{
		$this->ci->load->model('effects/e_kujikiri');
		$this->ci->e_kujikiri->_attack(&$actor);
	}
	
	function hit(&$vic, &$actor, &$hit)
	{
		$hit['dmg'] += 3;
	}
}