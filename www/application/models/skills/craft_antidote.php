<?php if(! defined('BASEPATH')) exit();

class craft_antidote extends CI_Model
{
	private $ci;
	private $cost;
	
	function craft_antidote()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('craft_antidote');
	}
	
	function fire(&$actor)
	{
		if($this->ci->actor->isOverEncumbered($actor['actor']))
			return array("You do not have room in your inventory.");
		$ret = $this->ci->actor->addEffect('exertion', $actor);
		$this->ci->load->model('pdata');
		$cnt = $this->ci->pdata->get('effect', 'exertion', $actor['actor']);
		
		if($cnt < 16)
		{
			$cnt += 3;
			$this->ci->pdata->set('effect', 'exertion', $cnt, $actor['actor']);
		}
		else		
			return array("You are far too fatigued for pharmacology.");
		
		foreach($ret as $r) $msg[] = $r;
		$this->ci->load->model('item');
		$sql = 'insert into actor_item (actor, inum) values (?, ?)';
		$this->db->query($sql, array($actor['actor'],
			$this->ci->item->getByName('antidote')));
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = "You craft an antidote.";
		return $msg;
	}
	
	function show()
	{
		return true;
	}
}
