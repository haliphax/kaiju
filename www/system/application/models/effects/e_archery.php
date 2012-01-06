<?php if(! defined('BASEPATH')) exit();

class e_archery extends Model
{
	private $ci;
	
	function e_archery()
	{
		parent::Model();
		$this->ci =& get_instance();
	}

	function attack(&$victim, &$actor, &$swing)
	{
		$this->ci->load->model('actor');
		$this->ci->load->model('item');
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		if($this->ci->item->hasClass($weps[0]['instance'], 'bow'))
			$swing['crit'] += 3;
	}
}