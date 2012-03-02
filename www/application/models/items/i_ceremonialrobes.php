<?php if(! defined('BASEPATH')) exit();

class i_ceremonialrobes extends CI_Model
{
	private $ci;
	
	function i_ceremonialrobes()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function equip(&$actor, &$instance)
	{
		return $this->ci->actor->addEffect('healinghands', $actor);
	}
	
	function remove(&$actor, &$instance)
	{
		return $this->ci->actor->removeEffect('healinghands', $actor);
	}
}