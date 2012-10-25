<?php if(! defined('BASEPATH')) exit();

class e_kujiin_kyo extends Model
{
	private $ci;
	
	function e_kujiin_kyo()
	{
		parent::Model();
		$this->ci =& get_instance();
	}

	function attack(&$vic, &$actor, &$swing)
	{
		$this->ci->load->model('effects/e_kujikiri');
		$this->ci->e_kujikiri->_attack(&$actor);
	}
	
	function chancetohit(&$actor)
	{
		$this->ci->load->model('effects/e_karate');
		if($this->e_karate->chancetohit(&$actor) == 5) return 2;
	}
}