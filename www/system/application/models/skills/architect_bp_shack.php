<?php if(! defined('BASEPATH')) exit();

class architect_bp_shack extends Model
{
	private $ci;
	private $cost;
	
	function architect_bp_shack()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('architect_bp_shack');
	}
	
	function fire(&$actor)
	{
		$ret = $this->ci->actor->addEffect('exertion', $actor);
		$this->ci->load->model('pdata');
		$cnt = $this->ci->pdata->get('effect', 'exertion', $actor['actor']);
		
		if($cnt < 16)
		{
			$cnt += 3;
			$this->ci->pdata->set('effect', 'exertion', $cnt, $actor['actor']);
		}
		else		
			return array("You are far too fatigued for architecture.");
		
		foreach($ret as $r) $msg[] = $r;
		$this->ci->load->model('item');
		$sql = 'insert into actor_item (actor, inum) values (?, ?)';
		$this->db->query($sql, array($actor['actor'],
			$this->ci->item->getByName('blueprint: shack')));
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;
		$msg[] = "You craft a set of Shack blueprints.";
		return $msg;
	}
}
