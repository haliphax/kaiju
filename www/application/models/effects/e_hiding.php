<?php if(! defined('BASEPATH')) exit();

class e_hiding extends NoCacheModel
{
	private $ci;
	
	function e_hiding()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('actor');
	}
	
	function on(&$actor)
	{
		$this->ci->load->model('pdata');
		$this->ci->pdata->set('effect', 'hiding', 108, $actor['actor']);
		return array('You vanish from sight.');
	}
	
	function off($actor)
	{
		$sql = <<<SQL
			delete from actor_effect where actor = ?
				and effect in
				(select effect from effect where abbrev = 'hiding')
SQL;
		$this->db->query($sql, array($actor['actor']));
		$this->ci->load->model('pdata');
		$this->ci->pdata->clear('effect', 'hiding', $actor['actor']);
		return array('You are no longer hidden.');
	}
	
	function ap($ap, &$actor)
	{
		return $this->off($actor);
	}
	
	function tick()
	{
		$sql = <<<SQL
			update actor set evts = 1 where actor in
				(select owner from pdata where dtype = 'effect'
				and dkey = 'hiding')
SQL;
		$this->db->query($sql);
		$sql = <<<SQL
			update pdata set dval = cast(dval as signed) - 1
			where dtype = 'effect' and dkey = 'hiding'
SQL;
		$this->db->query($sql);
		$sql = <<<SQL
			select owner from pdata
			where dtype = 'effect' and dkey = 'hiding'
				and cast(dval as signed) <= 0
SQL;
		$q = $this->db->query($sql);
		$r = $q->result_array();
		foreach($r as $row)
			$this->ci->actor->sendEvent(
				"You grow weary, and cannot hide any longer.", $row['owner']);
		$sql = <<<SQL
			delete from actor_effect where effect in
				(select effect from effect where abbrev = 'hiding')
				and actor in
				(select owner from pdata where dtype = 'effect'
					and dkey = 'hiding' and cast(dval as signed) <= 0)
SQL;
		$this->db->query($sql);
		$sql = <<<SQL
			delete from pdata where dtype = 'effect' and dkey = 'hiding'
				and cast(dval as signed) <= 0
SQL;
		$this->db->query($sql);
	}
}