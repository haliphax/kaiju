<?php if(! defined('BASEPATH')) exit();

class majutsu extends Model
{
	private $ci;
	
	# constructor
	function majutsu()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
	}

	# purchase skill
	function purchase(&$actor)
	{
		$this->ci->actor->incStat('mp', 5, $actor['actor']);
		$this->ci->actor->incStat('mpmax', 5, $actor['actor']);
	}
}
