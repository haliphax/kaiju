<?php if(! defined('BASEPATH')) exit();

class e_ardentpassion extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function chancetohit(&$actor, &$victim)
	{
		if($this->ci->actor->hasEffect('dance_mtnpath', $actor['actor']))
			return 2;
	}
}