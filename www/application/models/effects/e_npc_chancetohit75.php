<?php if(! defined('BASEPATH')) exit();

class e_npc_chancetohit75 extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}
	
	function chancetohit()
	{
		return 15;
	}
}
