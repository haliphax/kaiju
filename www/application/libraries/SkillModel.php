<?php if(! defined('BASEPATH')) exit();

class SkillModel extends CI_Model
{
	protected $cost;
	protected $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost(get_class($this));
	}
}
