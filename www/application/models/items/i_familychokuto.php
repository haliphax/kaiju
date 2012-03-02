<?php if(! defined('BASEPATH')) exit();

class i_familychokuto extends CI_Model
{
	private $ci;
	
	function i_familychokuto()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function equip(&$actor, &$instance)
	{
		if($actor['stat_hp'] <= ($actor['stat_hpmax'] * 0.2))
		{
			$instance = 0;
			return array(
				"The chokuto will not allow itself to be held by someone so weak."
				);
		}
		
		$msg = array();
		$ret = $this->ci->actor->addEffect('ancestralrage', $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function remove(&$actor, &$instance)
	{
		$msg = array();
		$ret = $this->ci->actor->removeEffect('ancestralrage', $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
}