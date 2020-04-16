<?php if(! defined('BASEPATH')) exit();

class e_poisondeadly extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function tick()
	{
		$res = $this->ci->effects->getActorsWith('poisondeadly');
		if(! $res) return false;
		$ret = array();
		$actors = array();
		$loaded = false;
		
		foreach($res as $r)
		{
			$actors[] = $r['actor'];
			$ret[] = "{$r['actor']} - Poison";
			$this->ci->actor->damage(1, $r);
			
			# died of poison
			if($r['stat_hp'] == 1)
			{
				if(! $loaded)
				{
					$this->ci->load->model('map');
					$loaded = true;
				}
				
				$this->ci->map->sendCellEvent(
					"{$r['aname']} succumbed to poison and died.",
					array($r['actor']), $r['map'], $r['x'], $r['y'],
					$r['indoors']);
				$this->ci->map->setRadiusEvtM($r['map'], $r['x'], $r['y'],
					$r['building']);
				$this->ci->map->setCellEvtS($r['map'], $r['x'], $r['y'],
					$r['indoors']);
			}
		}
		
		$this->ci->actor->sendEvent(
			'As time wears on, you writhe in pain from the poison in your '
			. 'blood.', $actors);
		
		$this->ci->actor->setStatFlag($actors);
		return $ret;
	}
	
	function on($actor)
	{
		$this->ci->load->model('effects/e_poison');
		return $this->ci->e_poison->on($actor);
	}
	
	function off($actor)
	{
		$this->ci->load->model('effects/e_poison');
		return $this->ci->e_poison->off($actor);
	}
	
	function ap($actor)
	{
		$this->ci->load->model('effects/e_poison');
		return $this->ci->e_poison->ap($actor);
	}		
}
