<?php if(! defined('BASEPATH')) exit();

class e_defense_ranged extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}

	function defend(&$vic, &$actor, &$swing)
	{
		if($swing['wep']['distance'] != 'melee')
			$swing['chance']--;
	}
}