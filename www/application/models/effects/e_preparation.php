<?php if(! defined('BASEPATH')) exit();

class e_preparation extends NoCacheModel
{
	private $ci;
	
	function e_preparation()
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