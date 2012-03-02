<?php if(! defined('BASEPATH')) exit();

class e_healinghands extends CI_Model
{
	private $ci;
	
	function e_healinghands()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
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