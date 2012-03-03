<?php if(! defined('BASEPATH')) exit();

class dance_mtnpath extends NoCacheModel
{
	private $ci;
	
	function dance_mtnpath()
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