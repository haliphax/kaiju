<?php if(! defined('BASEPATH')) exit();

class e_npc_counterattack extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}

	function defend(&$victim, &$actor, &$swing)
	{
		$this->ci->load->model('tdata');
		
		if($this->ci->tdata->get('counteratk') === 1
			|| rand(1, 20) > 15)
		{
			return;
		}
		
		$this->ci->tdata->set('counteratk', 1);
		$res = $this->ci->actor->attack($actor['actor'], &$victim);
		foreach($res as $r) $this->ci->actor->sendEvent($r, $victim['actor']);
	}
}
