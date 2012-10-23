<?php if(! defined('BASEPATH')) exit();

class e_insp_dance_fallingleaf extends Model
{
	private $ci;
	
	function e_insp_dance_fallingleaf()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function struck(&$victim, &$actor, &$hit)
	{
		if(! $hit['hit'])
			return false;
		
		# damage mitigation chance: 5%
		if(rand(1, 20) == 1)
		{
			$hit['hit'] = false;
			$msg[] = "{$victim['aname']} brushes aside with your blow, as a falling leaf caught by the wind.";
			$this->ci->actor->sendEvent(
				"You brushed aside with {$actor['aname']}'s blow, as a falling leaf caught by the wind.",
				$victim['actor']);
			return $msg;
		}
		
		return false;
	}
	
	function on(&$actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->set('effect', 'insp_dance', 1, $actor['actor'], 44);
		return array("You feel more relaxed and aloof.");
	}
	
	function off(&$actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->clear('effect', 'insp_dance', $actor['actor'], 44);
		return array("You feel less relaxed and aloof.");
	}
	
	function tick()
	{
		$sql = <<<SQL
			select owner from pdata where dtype = 'effect'
				and dkey = 'insp_dance' and altkey = 44
			and cast(dval as signed) < 0
SQL;
		$q = $this->db->query($sql);
		$r = $q->result_array();
		
		foreach($r as $row)
		{
			$who = $this->ci->actor->getInfo($row['owner']);
			$ret = $this->ci->actor->removeEffect('insp_dance_fallingleaf',
				$who);
			foreach($ret as $rr)
				$this->ci->actor->sendEvent($rr, $who['actor']);
			$this->ci->actor->setStatFlag($who['actor']);
		}
	}
}