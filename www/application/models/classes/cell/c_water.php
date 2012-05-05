<?php if(! defined('BASEPATH')) exit();

class c_water extends CI_Model
{
	private $ci;
	
	function c_water()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function arrive(&$actor)
	{
		if(! $this->ci->actor->hasSkill('swim', $actor['actor']))
			return array(false, array("You can't swim."));
		$this->ci->load->model('pdata');
		$msg = array();
		$ret = $this->ci->actor->addEffect('exertion', $actor);
		foreach($ret as $r) $msg[] = $r;
		$cnt = $this->ci->pdata->get('effect', 'exertion', $actor['actor']);
		if($cnt < 16)
			$this->ci->pdata->set('effect', 'exertion', ++$cnt,
				$actor['actor']);
		$ret = 0;
		
		if($cnt < 6)
		{
			$msg[] = "You swim through the water with minor difficulty.";
			$ret = $this->ci->actor->spendAP(2, &$actor);
		}
		else if($cnt < 11)
		{
			$msg[] = "Fighting against the pain in your lungs, you swim on.";
			$ret = $this->ci->actor->spendAP(3, &$actor);
		}
		else if($cnt < 16)
		{
			$msg[] =
				"Your muscles burn like they're on fire, but you continue swimming.";
			$ret = $this->actor->damage(1, &$actor);
			foreach($ret as $r) $msg[] = $r;
			$ret = $this->ci->actor->spendAP(5, $actor);
		}
		else
		{
			$msg[] = "You can't swim any more. You can barely tread water.";
			return array(false, $msg);
		}
		
		foreach($ret as $r) $msg[] = $r;
		return array(true, $msg);
	}
}