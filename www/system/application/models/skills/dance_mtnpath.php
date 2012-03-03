<?php if(! defined('BASEPATH')) exit();

class dance_mtnpath extends Model
{
	private $ci;
	
	function dance_mtnpath()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('skills');
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('dance', $actor['actor']);
	}
}