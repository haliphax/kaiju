<?php if(! defined('BASEPATH')) exit();

class e_poison extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function on(&$actor)
	{
		$this->ci->actor->setStatFlag($actor['actor']);
		return array('You feel poison begin to course through your body.');
	}
	
	function off(&$actor)
	{
		$this->ci->actor->setStatFlag($actor['actor']);
		return array('The poison in your veins dissapates.');
	}
	
	function ap($ap, &$actor)
	{
		$this->ci->actor->damage(1, &$actor);
		return array('You take damage from poison.');
	}
}