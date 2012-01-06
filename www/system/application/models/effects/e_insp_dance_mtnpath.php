<?php if(! defined('BASEPATH')) exit();

class e_insp_dance_mtnpath extends Model
{
	private $ci;
	
	function e_insp_dance_mtnpath()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function armor($actor, &$armor)
	{
		foreach($armor as $k => $a)
			$armor[$k]++;
	}
	
	function on(&$actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->set('effect', 'insp_dance', 1, $actor['actor'], 40);
		return array("You feel more stalwart and alert.");
	}
	
	function off(&$actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->clear('effect', 'insp_dance', $actor['actor'], 40);
		return array("You feel less stalwart and alert.");
	}
	
	function tick()
	{
		$sql = <<<SQL
			select owner from pdata where dtype = 'effect'
				and dkey = 'insp_dance' and altkey = 40
			and cast(dval as signed) < 0
SQL;
		$q = $this->db->query($sql);
		$r = $q->result_array();
		
		foreach($r as $row)
		{
			$who = $this->ci->actor->getInfo($row['owner']);
			$ret = $this->ci->actor->removeEffect('insp_dance_mtnpath', $who);
			foreach($ret as $rr)
				$this->ci->actor->sendEvent($rr, $who['actor']);
			$this->ci->actor->setStatFlag($who['actor']);
		}
	}
}