<?php if(! defined('BASEPATH')) exit();

class e_combat extends CI_Model
{
	private $ci;
	
	function e_combat()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}
	
	function chancetohit()
	{
		return 5;
	}
}
