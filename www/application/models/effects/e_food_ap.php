<?php if(! defined('BASEPATH')) exit();

class e_food_ap extends CI_Model
{
	private $ci;
	
	function e_food_ap()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function disp(&$actor)
	{
		$this->ci->load->model('pdata');
		$cnt = $this->ci->pdata->get('effect', 'food_ap', $actor['actor']);
		return "Well fed (AP): {$cnt}";
	}
	
	function on(&$actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->set('effect', 'food_ap', 12, $actor['actor']);
		return array("You are well fed, and instilled with energy.");
	}
	
	function off(&$actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->clear('effect', 'food_ap', $actor['actor']);
		return array("The warm, fuzzy feeling in your belly fades away.");
	}
	
	function tick()
	{
		$this->ci->load->model('actor');
		$sql = <<<SQL
			update pdata set dval = cast(dval as signed) - 1
			where dtype = 'effect' and dkey = 'food_ap'
SQL;
		$this->db->query($sql);
		$sql = <<<SQL
			update actor set stat_ap = stat_ap + 1 where actor in
			(select owner from pdata where dtype = 'effect'
				and dkey = 'food_ap')
			and stat_ap < stat_apmax
SQL;
		$this->db->query($sql);
		$sql = <<<SQL
			update actor set evts = 1 where actor in
			(select owner from pdata where dtype = 'effect'
				and dkey = 'food_ap')
SQL;
		$this->db->query($sql);
		$sql = <<<SQL
			select owner from pdata where dtype = 'effect' and dkey = 'food_ap'
			and cast(dval as signed) <= 0
SQL;
		$q = $this->db->query($sql);
		$r = $q->result_array();
		
		foreach($r as $row)
		{
			$who = $this->ci->actor->getInfo($row['owner']);
			$ret = $this->ci->actor->removeEffect('food_ap', $who);
			foreach($ret as $rr)
				$this->ci->actor->sendEvent($rr, $who['actor']);
		}
		
		$sql = <<<SQL
			delete from pdata where dtype = 'effect' and dkey = 'food_ap'
			and cast(dval as signed) <= 0
SQL;
		$this->db->query($sql);
	}
}