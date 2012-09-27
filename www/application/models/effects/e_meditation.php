<?php if(! defined('BASEPATH')) exit();

class e_meditation extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function on(&$actor)
	{
		return array(
			"You close your eyes as you clear your mind and draw your metaphysical attention inward."
			);
	}
	
	function defend(&$vic, &$actor, &$swing)
	{
		$this->ci->actor->removeEffect('meditation', &$vic);
		$this->ci->actor->sendEvent(
			"You were attacked by {$actor['aname']}, and your concentration was broken!",
			$vic['actor']);
		return array("Their meditative trance has been interrupted!");
	}
	
	function ap($ap, &$actor)
	{
		$this->ci->actor->removeEffect('meditation', $actor);
		return array("Your meditative trance has been broken.");
	}
	
	function tick()
	{
		$s = <<<SQL
			update actor set stat_mp = stat_mp + 1, evts = 1
			where actor in
				(select actor from actor_effect ae
				join effect e on ae.effect = e.effect
				where abbrev = 'meditation')
			and stat_mp < stat_mpmax and rand() >= 0.5
SQL;
		$this->db->query($s);
	}
}