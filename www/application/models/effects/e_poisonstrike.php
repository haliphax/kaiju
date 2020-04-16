<?php if(! defined('BASEPATH')) exit();

class e_poisonstrike extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function on($actor)
	{
		return array('You coat your weapon with poison.');
	}
	
	function hit(&$victim, &$actor, &$hit)
	{
		if(! $hit['hit']) return false;
		$ret = $this->ci->actor->addEffect('poison', $victim);
		foreach($ret as $r)
			if($r) $this->ci->actor->sendEvent($r, $victim['actor']);
		return array('You have poisoned your victim.');
	}
}
