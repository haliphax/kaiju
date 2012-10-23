<?php if(! defined('BASEPATH')) exit();

class i_ceremonialrobes extends Model
{
	private $ci;
	
	function i_ceremonialrobes()
	{
		parent::Model();
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