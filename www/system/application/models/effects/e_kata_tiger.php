<?php if(! defined('BASEPATH')) exit();

class e_kata_tiger extends Model
{
	private $ci;
	
	function e_kata_tiger()
	{
		parent::Model();
		$this->ci =& get_instance();
	}
	
	function attack(&$victim, &$actor, &$swing)
	{
		if($swing['wep']['iname'] == 'fists')
			$swing['wep']['dmg_type'] = 'slashing';
	}
	
	function on()
	{
		return array("Your fists become like the claws of the Tiger.");
	}
}