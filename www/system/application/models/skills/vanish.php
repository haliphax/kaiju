<?php if(! defined('BASEPATH')) exit();

class vanish extends Model
{
	private $ci;
	private $cost;
	
	# constructor
	function vanish()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('vanish');
	}

	# use skill
	function fire(&$actor)
	{
		if(! $this->show(&$actor)) return false;
		if($actor['stat_mp'] < $this->cost['cost_mp'])
			return $this->ci->skills->nomp;
		$msg = array();
		$res = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($res as $r) $msg[] = $r;
		$res = $this->ci->actor->spendMP($this->cost['cost_mp'], &$actor);
		foreach($res as $r) $msg[] = $r;
		$roll = rand(1, 20);
		
		if($roll > 1)
		{
			$msg[] = "You conceal yourself in mystical smoke.";
			$res = $this->ci->actor->addEffect('hiding', &$actor);
			foreach($res as $r) $msg[] = $r;
		}
		else
			$msg[] = "You attempt to vanish, but your concentration is broken!";
		
		return $msg;
	}
	
	# show skill?
	function show(&$actor)
	{
		if($this->ci->actor->hasEffect('hiding', $actor['actor'])) return false;
		if(! $actor['indoors']) return false;
		return true;
	}
}
