<?php if(! defined('BASEPATH')) exit();

class i_antidote extends Model
{
	private $ci;
	
	function i_antidote()
	{
		parent::Model();
		$this->ci =& get_instance();
	}

	function fire(&$item, &$actor, &$victim)
	{
		$this->ci->load->model('actor');
		if(! $victim) $victim = $actor;
		$this->ci->actor->spendAP(1, &$actor);
		if($victim['stat_hp'] <= 0)
			return array('They are dead. Whatever poison they suffered from '
				. 'has claimed their life already.');
		
		if(! $this->ci->actor->hasEffect('poison', $victim['actor'])
			&& ! $this->ci->actor->hasEffect('poisondeadly', $victim['actor']))
		{
			if($victim['actor'] == $actor['actor'])
				return array('You are not suffering from poison.');
			return array('They are not under the effects of any poison.');
		}
		
		$this->ci->actor->dropItems(array($item['instance']), $actor['actor']);
		$msg = array();
		$omsg = array();
		
		# self
		if($actor['actor'] == $victim['actor'])
			$msg[] = 'You apply the antidote to yourself, curing your '
				. 'poison.';
		# others
		else
		{
			$omsg[] = "{$actor['aname']} used an antidote on you and relieved "
				. "your poisonous affliction.";
			$msg[] = "You apply the antidote to {$victim['aname']}, curing "
				. "their poison.";
		}
		
		$ret = $this->ci->actor->removeEffect('poison', &$victim);
		foreach($ret as $r) $omsg[] = $r;
		$ret = $this->ci->actor->removeEffect('poisondeadly', &$victim);
		foreach($ret as $r) $omsg[] = $r;
		foreach($omsg as $o)
			$this->ci->actor->sendEvent($o, $victim['actor']);
		return $msg;
	}
}