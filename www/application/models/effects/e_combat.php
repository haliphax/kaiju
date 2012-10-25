<?php if(! defined('BASEPATH')) exit();

class e_combat extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function chancetohit()
	{
		return 5;
	}
}
