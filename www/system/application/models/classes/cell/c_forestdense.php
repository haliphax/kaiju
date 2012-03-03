<?php if(! defined('BASEPATH')) exit();

class c_forestdense extends Model
{
	private $ci;
	
	function c_forestdense()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}

	function arrive(&$actor)
	{
		$msg = array();
		
		if($this->ci->actor->hasEffect('climbing', $actor['actor']))
		{
			$msg[] =
				"You climb through the treetops, avoiding the impeding underbrush.";
			$ret = $this->ci->actor->spendAP(1, &$actor);
			foreach($ret as $r) $msg[] = $r;		
		}	
		else
		{
			$msg[] = "You slowly fight your way through the dense underbrush.";
			$ret = $this->ci->actor->spendAP(2, &$actor);
			foreach($ret as $r) $msg[] = $r;
		}
		
		return array(true, $msg);
	}
}