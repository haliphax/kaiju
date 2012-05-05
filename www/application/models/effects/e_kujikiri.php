<?php if(! defined('BASEPATH')) exit();

class e_kujikiri extends CI_Model
{
	private $ci;
	
	function __construct()
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