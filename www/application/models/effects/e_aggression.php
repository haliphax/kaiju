<?php if(! defined('BASEPATH')) exit();

class e_aggression extends CI_Model
{
	private $ci;
	
	function e_aggression()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function hit(&$victim, &$actor, &$hit)
	{
		if($this->ci->actor->hasEffect('dance_fallingleaf', $actor['actor']))
			$hit['dmg'] = round($hit['dmg'] * 1.1);
	}
}