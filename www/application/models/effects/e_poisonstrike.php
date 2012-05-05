<?php if(! defined('BASEPATH')) exit();

class e_poisonstrike extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function on($actor)
	{
		return array('You coat your weapon with poison.');
	}
	
	function hit(&$victim, &$actor, &$hit)
	{
		if(! $hit['hit']) return false;
		$this->ci->load->model('actor');
		$ret = $this->ci->actor->addEffect('poison', &$victim);
		foreach($ret as $r)
			if($r) $this->ci->actor->sendEvent($r, $victim['actor']);
		return array('You have poisoned your victim.');
	}
}