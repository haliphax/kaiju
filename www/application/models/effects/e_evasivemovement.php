<?php if(! defined('BASEPATH')) exit();

class e_evasivemovement extends NoCacheModel
{
	private $ci;
	
	function e_evasivemovement()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}

	function struck(&$victim, &$actor, &$hit)
	{
		if(! $hit['hit'])
			return false;
		
		# damage mitigation chance: 5%
		if(rand(1, 20) == 1)
		{
			$hit['hit'] = false;
			$msg[] = "{$victim['aname']} brushes aside with your blow, as a falling leaf caught by the wind.";
			$this->ci->actor->sendEvent(
				"You brushed aside with {$actor['aname']}'s blow, as a falling leaf caught by the wind.",
				$victim['actor']);
			return $msg;
		}
		
		return;
	}
}