<?php if(! defined('BASEPATH')) exit();

class song_luminescentsoul extends NoCacheModel
{
	private $ci;
	
	function song_luminescentsoul()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('skills');
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('sing', $actor['actor']);
	}
}