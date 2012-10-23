<?php if(! defined('BASEPATH')) exit();

class ascend extends CI_Model
{
	private $ci;
	
	function ascend()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function fire(&$actor, &$retval, $args)
	{
		if(! $this->show(&$actor)) return false;
		$class = $args[0];
		if($class < 2 || $class > 4) return false;
		$this->ci->load->model('actor');
		$s = 'insert into actor_class (actor, aclass) values (?, ?)';
		
		if($class == 2)
		{
			$this->ci->actor->incStat('mp', 30, $actor['actor']);
			$this->ci->actor->incStat('mpmax', 30, $actor['actor']);
		}
		else if($class  == 3)
		{
			$this->ci->actor->incStat('mp', 20, $actor['actor']);
			$this->ci->actor->incStat('mpmax', 20, $actor['actor']);
		}
		
		$this->db->query($s, array($actor['actor'], $class));
		return array("New skills are now available for you to purchase.");
	}
	
	function params()
	{
		return array(
			array(2, 'Monk'),
			array(3, 'Ninja'),
			array(4, 'Samurai'),
			array(5, 'Entrepeneur'),
			);
	}
	
	function show(&$actor)
	{
		if($actor['stat_xpspent'] < 750) return false;
		$s = 'select count(1) as cnt from actor_class where actor = ?';
		$q = $this->db->query($s, array($actor['actor']));
		$r = $q->row_array();
		if($r['cnt'] > 1) return false;
		return true;
	}	
}
