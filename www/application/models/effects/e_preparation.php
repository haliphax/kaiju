<?php if(! defined('BASEPATH')) exit();

class e_preparation extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function armor(&$armor, $actor)
	{
		foreach($armor as $k => $a)
			$armor[$k]++;
	}
}