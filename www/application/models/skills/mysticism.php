<?php if(! defined('BASEPATH')) exit();

class mysticism extends NoCacheModel
{
	private $ci;
	
	# constructor
	function mysticism()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}

	# purchase skill
	function purchase(&$actor)
	{
		$this->ci->actor->incStat('mp', 5, $actor['actor']);
		$this->ci->actor->incStat('mpmax', 5, $actor['actor']);
	}
}