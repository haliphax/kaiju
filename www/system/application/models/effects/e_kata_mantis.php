<?php if(! defined('BASEPATH')) exit();

class e_kata_mantis extends Model
{
	private $ci;
	
	function e_kata_mantis()
	{
		parent::Model();
		$this->ci =& get_instance();
	}
	
	function attack(&$victim, &$actor, &$swing)
	{
		if($swing['wep']['iname'] == 'fists')
			$swing['wep']['dmg_type'] = 'piercing';
	}
	
	function on()
	{
		return array("You fold your arms in the style of the Preying Mantis.");
	}
}