<?php if(! defined('BASEPATH')) exit();

class e_archery extends EffectModel
{
	
	function __construct()
	{
		parent::__construct();
	}

	function attack(&$victim, &$actor, &$swing)
	{
		$this->ci->load->model('item');
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		if($this->ci->item->hasClass($weps[0]['instance'], 'bow'))
			$swing['crit'] += 3;
	}
}