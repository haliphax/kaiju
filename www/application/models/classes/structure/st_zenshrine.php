<?php if(! defined('BASEPATH')) exit();

class st_zenshrine extends CI_Model
{
	private $ci;
	
	function st_zenshrine()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function b_enter(&$actor)
	{
		$this->ci->actor->addEffect('soothed', $actor);
		return array(true, array(
			"You feel waves of soothing energy pass over you as you enter the shrine."
			));
	}
	
	function b_exit(&$actor)
	{
		$this->ci->actor->removeEffect('soothed', $actor);
		return array(true, array(
			"The soothing feeling leaves your body as you exit the shrine."
			));
	}
}
