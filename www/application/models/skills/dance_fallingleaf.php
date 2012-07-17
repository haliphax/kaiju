<?php if(! defined('BASEPATH')) exit();

class dance_fallingleaf extends SkillModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('dance', $actor['actor']);
	}
}