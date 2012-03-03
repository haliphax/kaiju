<?php if(! defined('BASEPATH')) exit();

class e_kujikiri extends NoCacheModel
{
	private $ci;
	
	function e_kujikiri()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}
	
	function chancetohit(&$actor)
	{
		$this->ci->load->model('effects/e_karate');
		return $this->ci->e_karate->chancetohit(&$actor);
	}
	
	# helper
	function _attack(&$actor)
	{
		$this->ci->load->model('actor');
		$this->ci->actor->incStat('ap', -1, $actor['actor']);
	}
}