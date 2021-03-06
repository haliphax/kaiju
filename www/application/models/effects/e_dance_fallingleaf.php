<?php if(! defined('BASEPATH')) exit();

class e_dance_fallingleaf extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
		$this->ci->load->model('map');
		$this->ci->load->model('pdata');
	}
	
	function on(&$actor)
	{
		$this->ci->map->sendCellEvent("{$actor['aname']} begins dancing.",
			array($actor['actor']), $actor['map'], $actor['x'], $actor['y'],
			$actor['indoors']);
		$this->apply_effect($actor);
		return array("You begin the Dance of the Falling Leaf, inspiring your allies to be relaxed and aloof.");
	}
	
	function tick()
	{
		$actors = $this->ci->effects->getActorsWith('dance_fallingleaf');		
		foreach($actors as $actor)
			$this->apply_effect($actor);
	}
	
	function apply_effect(&$actor)	
	{
		$occs = $this->ci->map->getCellOccupants($actor['map'], $actor['x'],
			$actor['y'], $actor['indoors']);
		
		foreach($occs as $occ)
			if($occ['actor'] != $actor['actor']
				&& $occ['faction'] == $actor['faction'])
			{
				if(! $this->ci->actor->hasEffectLike('insp_dance_%',
					$occ['actor']))
				{
					$ret = $this->ci->actor->addEffect('insp_dance_fallingleaf',
						$occ);
					foreach($ret as $r)
						$this->ci->actor->sendEvent($r, $occ['actor']);
				}
				else if($this->ci->actor->hasEffect('insp_dance_fallingleaf',
					$occ['actor']))
				{
					$this->ci->pdata->set('effect', 'insp_dance', 1,
						$occ['actor'], 44);
				}
			}	
	}
}
