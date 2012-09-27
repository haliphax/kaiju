<?php if(! defined('BASEPATH')) exit();

class EffectModel extends CI_Model
{
	protected $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('actor');
		$this->ci->load->model('effects');
	}
}
