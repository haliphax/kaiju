<?php if(! defined('BASEPATH')) exit();

class e_poison extends CI_Model
{
	private $ci;
	
	function e_poison()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('actor');
	}
	
	function on(&$actor)
	{
		$this->ci->actor->setStatFlag($actor['actor']);
		return array('You feel poison begin to course through your body.');
	}
	
	function off(&$actor)
	{
		$this->ci->actor->setStatFlag($actor['actor']);
		return array('The poison in your veins dissapates.');
	}
	
	function ap($ap, &$actor)
	{
		$this->ci->actor->damage(1, &$actor);
		return array('You take damage from poison.');
	}
}