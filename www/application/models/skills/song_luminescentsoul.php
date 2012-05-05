<?php if(! defined('BASEPATH')) exit();

class song_luminescentsoul extends CI_Model
{
	private $ci;
	
	function __construct()
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