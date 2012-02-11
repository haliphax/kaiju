<?php if(! defined('BASEPATH')) exit();

class e_defense_ranged extends Model
{
	private $ci;
	
	function e_defense_ranged()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}

	function defend(&$vic, &$actor, &$swing)
	{
		if($swing['wep']['distance'] != 'melee')
			$swing['chance']--;
	}
}