<?php if(! defined('BASEPATH')) exit();

class e_npc_counterattack extends NoCacheModel
{
	private $ci;
	
	function e_npc_counterattack()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
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
		$this->ci->load->model('actor');
		$res = $this->ci->actor->attack($actor['actor'], &$victim);
		foreach($res as $r) $this->ci->actor->sendEvent($r, $victim['actor']);
	}
}
