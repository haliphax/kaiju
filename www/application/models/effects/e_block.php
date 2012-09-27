<?php if(! defined('BASEPATH')) exit();

class e_block extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function struck(&$victim, &$actor, &$hit)
	{
		if(! $hit['hit']) return false;
		# block chance: 15%
		$bc = 3;
		# block value: 2
		$bv = 2;
		$roll = rand(1, 20);
		
		# block!
		if($roll <= $bc)
		{
			$hit['dmg'] -= $bv;
			if($hit['dmg'] < 0) $hit['dmg'] = 0;
			$msg[] =
				"{$victim['aname']} blocked your attack, reducing its damage.";
			$this->ci->actor->sendEvent(
				"You blocked {$actor['aname']}'s attack, reducing its damage.",
				$victim['actor']);
			return $msg;
		}
		
		return false;
	}
}