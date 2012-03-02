<?php if(! defined('BASEPATH')) exit();

class kendo_kote extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function kendo_kote()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('kendo_kote');
	}

	# use skill
	function fire(&$victim, &$actor)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap']) 
			return $this->ci->skills->noap;
		$weps = $this->ci->actor->getWeapons($actor['actor']);
		$res = $this->ci->actor->attackWith(&$victim, $weps[0], 'arms',
			false, false, &$actor, $fail, $hit);
		foreach($res as $r) $msg[] = $r;
		
		if($hit['hit'])
		{
			$victim = $this->ci->actor->getInfo($victim['actor']);
			
			if($victim['stat_hp'] > 0)
			{
				$s = <<<SQL
					update actor_item set eq_slot = NULL
					where actor = ? and (eq_slot = 'MH' or eq_slot = 'OH')
					limit 1;
SQL;
				$this->db->query($s, array($victim['actor']));
				
				if($this->db->affected_rows() > 0)
				{
					$msg[] = "You have disarmed your opponent!";
					$this->ci->actor->sendEvent("You have been disarmed!",
						$victim['actor']);
				}
			}
		}
		
		$res = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($res as $r) $msg[] = $r;
		return $msg;
	}
	
	function purchase(&$actor)
	{
		$this->ci->actor->addSkill('kendo', $actor['actor']);
	}
}
