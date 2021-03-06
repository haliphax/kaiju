<?php if(! defined('BASEPATH')) exit();

class e_healinghands extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}
	
	function on(&$actor)
	{
		return array(
			'You are bathed in a white light, and your hands begin to tingle.');
	}
	
	function off(&$actor)
	{
		return array(
			'The white light leaves you, as does the sensation in your hands.');
	}
	
	function heal(&$victim, &$actor, &$heal)
	{
		$heal['hp'] = round($heal['hp'] * 1.35);
		return array('Your healing powers were magnified.');
	}
}