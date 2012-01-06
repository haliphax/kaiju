<?php if(! defined('BASEPATH')) exit();

class e_kujikiri extends Model
{
	private $ci;
	
	function e_kujikiri()
	{
		parent::Model();
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