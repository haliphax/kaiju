<?php if(! defined('BASEPATH')) exit();

class e_caltrops extends Model
{
	private $ci;
	
	function e_caltrops()
	{
		parent::Model();
		$this->ci =& get_instance();
	}

	function move($where, &$actor)
	{
		$this->ci->load->model('actor');
		$this->ci->actor->damage(5, &$actor);
		$this->ci->actor->removeEffect('caltrops', &$actor);
		return array("Pain shoots through your feet as you step on caltrops.");
	}
}