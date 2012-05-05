<?php if(! defined('BASEPATH')) exit();

class i_bandage extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}

	function fire(&$item, &$actor, &$victim)
	{
		$this->ci->load->model('actor');
		if(! $victim) $victim =& $actor;
		if($victim['stat_hp'] <= 0)
			return array('They are dead. Bandages cannot help them now.');
		if($victim['stat_hp'] >= $victim['stat_hpmax'])
			return array('No healing is necessary.');
		$this->ci->actor->spendAP(1, &$actor);
		$msg = array();
		$heal = array();
		$heal['hp'] = 5;
		$down = $victim['stat_hpmax'] - $victim['stat_hp'];
		if($down < $heal['hp']) $heal['hp'] = $down;
		$res = $this->ci->actor->heal(&$actor, &$victim, &$heal);
		foreach($res as $r) $msg[] = $r;
		$this->ci->actor->dropItems(array($item['instance']), $actor['actor']);
		
		# self
		if($actor['actor'] == $victim['actor'])
		{
			$msg[] = 'You apply bandages to your wounds, healing yourself '
				. "for {$heal['hp']}HP.";
			return $msg;
		}
		
		$this->ci->actor->sendEvent("{$actor['aname']} bandaged you for "
			. "{$heal['hp']}HP.", $victim['actor']);
		$msg[] = "You apply bandages to {$victim['aname']}, healing them "
			. "for {$heal['hp']}HP.";
		return $msg;
	}
}
