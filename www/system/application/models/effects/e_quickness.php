<?php if(! defined('BASEPATH')) exit();

class e_quickness extends Model
{
	private $ci;
	
	function e_quickness()
	{
		parent::Model();
		$this->ci =& get_instance();
	}

	function attack(&$vic, &$actor, &$swing)
	{
		if(rand(1, 20) > 3) return;
		$this->ci->load->model('actor');
		$this->ci->actor->incStat('ap', 1, $actor['actor']);
		return array(
			"You strike with quickness and fluidity, expending no energy whatsoever."
			);
	}
}