<?php if(! defined('BASEPATH')) exit();

class e_soothed extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function tick()
	{
		$s = <<<SQL
			update actor set stat_mp = stat_mp + 1
			where stat_mp < stat_mpmax
				and actor in (
					select actor from actor_effect ae
					join effect e on ae.effect = e.effect
					where abbrev = 'soothed'
				)
SQL;
		$this->db->query($s);
	}
	
	function on($actor)
	{
		return array("You are awash with a feeling of calm.");
	}
	
	function off($actor)
	{
		return array("The pervasive calm has left you.");
	}
	
	function attack(&$victim, &$actor, &$swing)
	{
		return $this->ci->actor->removeEffect('soothed', $actor);
	}
	
	function struck(&$victim, &$actor, &$hit)
	{
		$ret = $this->ci->actor->removeEffect('soothed', $victim);
		foreach($ret as $r)
			$this->ci->actor->sendEvent($r, $victim['actor']);
	}
}