<?php if(! defined('BASEPATH')) exit();

class rez extends CI_Model
{
	private $ci;
	private $cost;
	
	function rez()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('action');
		$this->cost = $this->ci->action->getCost('dead', 'rez');
	}
	
	function fire(&$actor, $retval)
	{
		if($actor['stat_hp'] > 0)
			return array('You cannot resurrect when you are already alive.');
		if($actor['stat_ap'] < 5)
			return array('Your spirit is too weak for the journey back.');
		$msg = array();
		$this->ci->load->model('pdata');
		$this->ci->pdata->clear('effect', 'decay', $actor['actor']);
		$this->ci->load->model('actor');
		$this->ci->actor->removeEffect('decayed', $actor);
		$this->ci->actor->incStat('ap', 0 - $this->cost, $actor['actor']);
		$this->ci->actor->setStat('hp', $actor['stat_hpmax'], $actor['actor']);
		$msg[] =
			'The Spirits have granted you a reprieve! Your body returns to life with a jolt.';
		$this->ci->load->model('map');
		$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'],
			$actor['y']);
		$this->ci->map->setCellEvtS($actor['map'], $actor['x'],
			$actor['y']);
		$this->ci->map->sendCellEvent(
			"{$actor['aname']} has returned from the dead.",
			array($actor['actor']), $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors']);
		return $msg;
	}
	
	function show(&$actor)
	{
		return true;
	}
}