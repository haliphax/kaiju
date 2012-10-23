<?php if(! defined('BASEPATH')) exit();

class e_kujikiri extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function chancetohit(&$actor)
	{
		$this->ci->load->model('effects/e_karate');
		return $this->ci->e_karate->chancetohit(&$actor);
	}
	
	# helper
	function _attack(&$actor)
	{
		$this->ci->actor->incStat('ap', -1, $actor['actor']);
	}
}