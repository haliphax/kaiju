<?php if(! defined('BASEPATH')) exit();

class song_luminescentsoul extends Model
{
	private $ci;
	
	function song_luminescentsoul()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('skills');
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('sing', $actor['actor']);
	}
}