<?php if(! defined('BASEPATH')) exit();

class e_poisonweapon extends NoCacheModel
{
	private $ci;
	
	function e_poisonweapon()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function on(&$actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->set('effect', 'poisonweapon', 36, $actor['actor']);
		return array("You coat your weapon with a powerful poison.");
	}
	
	function off(&$actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->clear('effect', 'poisonweapon', $actor['actor']);
	}
	
	function hit(&$victim, &$actor, &$hit)
	{
		if(! $hit['hit']) return;
		$this->ci->load->model('actor');
		$ret = $this->ci->actor->addEffect('poison', $victim);
		foreach($ret as $r)
			if($r) $this->ci->actor->sendEvent($r, $victim['actor']);
		$roll = rand(1, 3);
		
		if($roll == 1)
		{
			$this->ci->actor->removeEffect('poisonweapon', &$actor);
			return array(
				"You have poisoned your victim with the last bit of toxin on your weapon."
				);
		}
		
		return array("You have poisoned your victim.");
	}
	
	function disp(&$actor)
	{
		$this->ci->load->model('pdata');
		$cnt = $this->ci->pdata->get('effect', 'poisonweapon', $actor['actor']);
		if(! $cnt) $cnt = 0;
		return "Poisoned weapon: {$cnt}";
	}
	
	function tick()
	{
		$this->ci->load->model('actor');
		$s = <<<SQL
			update pdata set dval = cast(dval as signed) - 1
			where dtype = 'effect' and dkey = 'poisonweapon'
SQL;
		$this->db->query($s);
		$s = <<<SQL
			select owner as actor from pdata where dtype = 'effect'
				and dkey = 'poisonweapon' and cast(dval as signed) <= 0
SQL;
		$q = $this->db->query($s);
		$r = $q->result_array();
		
		foreach($r as $row)
		{
			$this->ci->actor->sendEvent(
				"The poison on your weapon has lost its effect.",
				$row['actor']
				);
			$this->ci->actor->removeEffect('poisonweapon', &$row);
		}
		
		return;
	}
}