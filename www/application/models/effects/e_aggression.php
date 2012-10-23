<?php if(! defined('BASEPATH')) exit();

class e_aggression extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function hit(&$victim, &$actor, &$hit)
	{
		if($this->ci->actor->hasEffect('dance_fallingleaf', $actor['actor']))
			$hit['dmg'] = round($hit['dmg'] * 1.1);
	}
}