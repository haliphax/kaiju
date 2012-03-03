<?php if(! defined('BASEPATH')) exit();

class e_kujiin_rin extends NoCacheModel
{
	private $ci;
	
	function e_kujiin_rin()
	{
		parent::__construct();
		$this->ci =& get_instance();
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