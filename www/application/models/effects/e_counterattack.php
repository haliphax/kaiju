<?php if(! defined('BASEPATH')) exit();

class e_counterattack extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}

	function defend(&$victim, &$actor, &$swing)
	{
		$this->ci->load->model('tdata');
		
		if($this->ci->tdata->get('counteratk') === 1
			|| rand(1, 20) > 2)
		{
			return;
		}
		
		$this->ci->tdata->set('counteratk', 1);
		$this->ci->actor->sendEvent(
			"You use this opportunity to counter-attack!", $victim['actor']);
		$res = $this->ci->actor->attack($actor['actor'], $victim);
		foreach($res as $r) $this->ci->actor->sendEvent($r, $victim['actor']);
		return array("Your attack granted them an opportunity!");
	}
}
