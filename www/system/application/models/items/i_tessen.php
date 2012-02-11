<?php if(! defined('BASEPATH')) exit();

class i_tessen extends Model
{
	private $ci;
	
	function i_tessen()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function equip(&$actor, &$instance)
	{
		if(! $this->ci->actor->hasClass('samurai', $actor['actor']))
		{
			$instance = 0;
			return array(
				"You do not understand how to wield this item."
				);
		}
		
		$msg = array();
		$ret = $this->ci->actor->addEffect('defense_ranged', $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
	
	function remove(&$actor, &$instance)
	{
		$msg = array();
		$ret = $this->ci->actor->removeEffect('defense_ranged', $actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}
}