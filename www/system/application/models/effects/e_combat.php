<?php if(! defined('BASEPATH')) exit();

class e_combat extends Model
{
	private $ci;
	
	function e_combat()
	{
		parent::Model();
		$this->ci =& get_instance();
	}
	
	function chancetohit()
	{
		return 5;
	}
}
