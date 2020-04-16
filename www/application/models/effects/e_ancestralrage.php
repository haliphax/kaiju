<?php if(! defined('BASEPATH')) exit();

class e_ancestralrage extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function on(&$actor)
	{
		$this->ci->actor->damage(1, $actor);
		return array('Your soul burns for the vengeance of your ancestors.');
	}
	
	function off(&$actor)
	{
		return array('The burning rage lifts from your being.');
	}
	
	function miss(&$victim, &$actor, &$hit)
	{
		$this->ci->load->model('tdata');
		if($this->ci->tdata->get('missed')) return false;
		$c = 7; # 35% chance
		$roll = rand(1, 20);		
		if($roll > $c) return false;
		$this->ci->tdata->set('missed', 1);
		$msg[] = 'You deftly shift your momentum and attack again.';
		$ret = $this->ci->actor->attackWith($victim, $hit['wep'], false, false,
			false, $actor, $fail);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function tick()
	{
		$this->ci->load->model('map');
		$res = $this->ci->effects->getActorsWith('e_ancestralrage');
		if(! $res) return false;
		$ret = array();
		$actors = array();
		
		foreach($res as $k => $r)
		{
			$this->ci->actor->sendEvent(
				"You wince in pain from the rage within your soul.",
				$r['actor']);
			$actors[] = $r['actor'];			
			$ret[] = "{$r['actor']} - Ancestral Rage";
			$this->ci->actor->damage(1, $r);
			
			# 1 hp left
			if($r['stat_hp'] == 2)
			{
				$weps = $this->ci->actor->getWeapons($r['actor']);
				
				foreach($weps as $w)
				{
					$this->ci->actor->sendEvent(
						"You are too weak to hold your weapons. You drop them before the rage consumes you completely.",
						$r['actor']);
					$res = $this->ci->actor->removeItems($w['instance'], $r);
					foreach($res as $m)
						$this->ci->actor->sendEvent($m, $r['actor']);
				}
			}			
			# died			
			else if($r['stat_hp'] == 1)
			{
				$this->ci->load->model('map');
				$this->ci->map->sendCellEvent(
					"{$r['aname']} collapsed and died.", array($r['actor']),
					$r['map'], $r['x'], $r['y'], $r['indoors']);
				$this->ci->map->setRadiusEvtM($r['map'], $r['x'], $r['y'],
					$r['building']);
				$this->ci->map->setCellEvtS($r['map'], $r['x'], $r['y'],
					$r['indoors']);
			}
		}
		
		$this->ci->actor->setStatFlag($actors);
		return $ret;
	}
}
