<?php if(! defined('BASEPATH')) exit();

class e_caltrops extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}

	function move($where, &$actor)
	{
		$this->ci->actor->damage(5, &$actor);
		$this->ci->actor->removeEffect('caltrops', &$actor);
		return array("Pain shoots through your feet as you step on caltrops.");
	}
}