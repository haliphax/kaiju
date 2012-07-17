<?php if(! defined('BASEPATH')) exit();

class song_luminescentsoul extends SkillModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('sing', $actor['actor']);
	}
}