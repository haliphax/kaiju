<?php if(! defined('BASEPATH')) exit();

class dance_fallingleaf extends NoCacheModel
{
	private $ci;
	
	function dance_fallingleaf()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('skills');
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('dance', $actor['actor']);
	}
}