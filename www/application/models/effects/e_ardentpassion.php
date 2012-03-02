<?php if(! defined('BASEPATH')) exit();

class e_ardentpassion extends CI_Model
{
	private $ci;
	
	function e_ardentpassion()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function chancetohit(&$actor, &$victim)
	{
		if($this->ci->actor->hasEffect('dance_mtnpath', $actor['actor']))
			return 2;
	}
}