<?php if(! defined('BASEPATH')) exit();

class e_karate extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function attack(&$victim, &$actor, &$swing)
	{
		if($swing['wep']['iname'] == 'fists'
			|| $swing['wep']['iname'] == 'Kick')
		{
			$swing['wep']['dmg_min'] += 2;
			$swing['wep']['dmg_max'] += 2;
		}
	}
	
	function chancetohit(&$actor)
	{
		$s = <<<SQL
			select 1 from actor_item
			where actor = ? and (eq_slot = 'MH' or eq_slot = 'OH')
SQL;
		$q = $this->db->query($s, array($actor['actor']));
		if($q->num_rows() > 0) return 0;
		return 5;
	}
}