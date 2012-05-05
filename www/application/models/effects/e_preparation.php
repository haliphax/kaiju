<?php if(! defined('BASEPATH')) exit();

class e_preparation extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}
	
	function armor(&$armor, $actor)
	{
		foreach($armor as $k => $a)
			$armor[$k]++;
	}
}