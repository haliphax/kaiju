<?php if(! defined('BASEPATH')) exit();

class i_food_rawfish extends CI_Model
{
	private $ci;
	
	function i_food_rawfish()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
	}

	function fire(&$item, &$actor)
	{
		$this->ci->load->model('actor');
		$sql = <<<SQL
			select 1 from actor_effect where effect in
			(select effect from effect where abbrev like 'food_%')
			and actor = ? 
SQL;
		$q = $this->db->query($sql, array($actor['actor']));
		if($q->num_rows() > 0)
			return array("You are already full.");
		$this->ci->actor->dropItems(array($item['instance']), $actor['actor']);
		$msg = array('You eat the raw fish.');
		$ret = $this->ci->actor->spendAP(1, $actor);
		foreach($ret as $r) $msg[] = $r;

		if(rand(1, 10) == 1)
		{
			$this->ci->actor->addEffect('poison', &$actor); 
			$msg[] = "Suddenly, you don't feel so good...";
		}
		else
		{
			if($actor['stat_hp'] < $actor['stat_hpmax'])
				$this->ci->actor->incStat('hp', 1, $actor['actor']);
		}		
		
		return $msg;
	}
}
