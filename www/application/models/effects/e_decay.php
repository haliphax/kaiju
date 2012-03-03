<?php if(! defined('BASEPATH')) exit();

class e_decay extends NoCacheModel
{
	private $ci;
	
	function e_decay()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('actor');
	}

	function tick()
	{
		$sql = <<<SQL
			insert ignore into pdata (dtype, owner, dkey, dval)
			select 'effect', actor, 'decay', 1 from actor
			where stat_hp <= 0 and actor not in
				(select a.actor from actor a
				join actor_effect ae on a.actor = ae.actor
				join effect e on ae.effect = e.effect
				where lower(ename) = 'decayed')
SQL;
		$this->db->query($sql);
		$sql = <<<SQL
			select * from pdata where dtype = 'effect' and dkey = 'decay'
				and cast(dval as signed) >= 72
SQL;
		$q = $this->db->query($sql);
		$r = $q->result_array();
		
		foreach($r as $row)
		{
			$who = $this->ci->actor->getInfo($row['owner']);
			$this->ci->actor->sendEvent(
				"Your lifeless body has decayed in the absence of your spirit.",
				$row['owner']);
			$this->ci->actor->addEffect('decayed', $who);
		}
		
		$sql = <<<SQL
			delete from pdata where dtype = 'effect' and dkey = 'decay'
				and cast(dval as signed) >= 72
SQL;
		$this->db->query($sql);
		
		$sql = <<<SQL
			update pdata set dval = (cast(dval as signed) + 1)
			where dtype = 'effect' and dkey = 'decay'
SQL;
		$this->db->query($sql);
	}
}