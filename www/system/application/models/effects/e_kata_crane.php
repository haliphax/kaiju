<?php if(! defined('BASEPATH')) exit();

class e_kata_crane extends Model
{
	private $ci;
	
	function e_kata_crane()
	{
		parent::Model();
		$this->ci =& get_instance();
	}
	
	function attack(&$victim, &$actor, &$swing)
	{
		if($swing['wep']['iname'] == 'fists')
			$swing['wep']['iname'] = 'Kick';
	}
	
	function hit(&$victim, &$actor, &$hit)
	{
		if($swing['wep']['iname'] == 'Kick')
			$hit['dmg'] += 1;
	}
	
	function on()
	{
		return array(
			"You raise your hands like the wings of the Crane, balancing on one leg."
			);
	}
}