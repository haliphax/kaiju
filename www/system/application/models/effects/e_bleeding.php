<?php if(! defined('BASEPATH')) exit();

class e_bleeding extends Model
{
	private $ci;
	
	function e_bleeding()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('actor');
	}
	
	function tick()
	{
		$this->ci->load->model('effects');
		$this->ci->load->model('map');
		$res = $this->ci->effects->getActorsWith('bleeding');
		if(! $res) return false;
		$ret = array();
		$actors = array();
		
		foreach($res as $r)
		{
			$actors[] = $r['actor'];
			$ret[] = "{$r['actor']} - Bleeding";
			$this->ci->actor->damage(1, &$r);
			
			# died of bleeding
			if($r['stat_hp'] == 1)
			{
				$this->ci->load->model('map');
				$this->ci->map->sendCellEvent(
					"{$r['aname']} bled to death.",
					array($r['actor']), $r['map'], $r['x'], $r['y'],
					$r['indoors']);
				$this->ci->map->setRadiusEvtM($r['map'], $r['x'], $r['y'],
					$r['building']);
				$this->ci->map->setCellEvtS($r['map'], $r['x'], $r['y'],
					$r['indoors']);
			}
		}
		
		$s = <<<SQL
			update pdata set dval = (cast(dval as signed) - 1)
			where dtype = 'effect' and dkey = 'bleeding'
SQL;
		$this->db->query($s);
		$s = <<<SQL
			select owner as actor from pdata where dtype = 'effect'
				and dkey = 'bleeding' and cast(dval as signed) <= 0
SQL;
		$q = $this->db->query($s);
		$r = $q->result_array();
		
		foreach($r as $row)
		{
			$ret = $this->ci->actor->removeEffect('bleeding', $row);
			foreach($ret as $rr)
				$this->ci->actor->sendEvent($rr, $row['actor']);			
		}
		
		$s = <<<SQL
			delete from pdata where dtype = 'effect' and dkey = 'bleeding'
				and cast(dval as signed) <= 0
SQL;
		$this->db->query($s);
		$this->ci->actor->sendEvent("Your body weakens as it loses blood.",
			$actors);
		
		$this->ci->actor->setStatFlag($actors);
		return $ret;
	}
	
	function on($actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->set('effect', 'bleeding', 12, $actor['actor']);
		return array("You are bleeding.");
	}
	
	function off($actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->clear('effect', 'bleeding', $actor['actor']);
		return array("You have stopped bleeding.");
	}
	
	function ap($ap, &$actor)
	{
		$who = $actor;
		if (! is_array($who)) $who = $this->ci->actor->getInfo($actor);
		$this->ci->actor->damage(1, &$who);
		return array("Your actions cause you to bleed further.");
	}
	
	function disp(&$actor)
	{
		$this->ci->load->model('pdata');
		$cnt = $this->ci->pdata->get('effect', 'bleeding', $actor['actor']);
		return "Bleeding: $cnt";	
	}
}