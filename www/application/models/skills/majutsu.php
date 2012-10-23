<?php if(! defined('BASEPATH')) exit();

class majutsu extends SkillModel
{
	
	# constructor
	function __construct()
	{
		parent::__construct();
	}

	# purchase skill
	function purchase(&$actor)
	{
		$this->ci->actor->incStat('mp', 5, $actor['actor']);
		$this->ci->actor->incStat('mpmax', 5, $actor['actor']);
	}
}
