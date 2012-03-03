<?php if(! defined('BASEPATH')) exit();

class e_parry extends NoCacheModel
{
	private $ci;
	
	function e_parry()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->load->database();
	}
	
	function struck(&$victim, &$actor, &$hit)
	{
		if(! $hit['hit'] || $hit['wep']['distance'] == 'ranged')
			return false;
		# parry chance: 15%
		$pc = 3;
		$roll = rand(1, 20);
		
		# parry!
		if($roll <= $pc)
		{
			$hit['hit'] = false;
			$msg[] = "{$victim['aname']} parried your attack!";
			$this->ci->actor->sendEvent(
				"You parried {$actor['aname']}'s attack!", $victim['actor']);
			return $msg;
		}
		
		return false;
	}
}