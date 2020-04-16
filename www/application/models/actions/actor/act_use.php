<?php if(! defined('BASEPATH')) exit();

class act_use extends CI_Model
{
	private $ci;
	
	function act_use()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('action');
		$this->ci->load->model('actor');
		$this->ci->load->model('item');
	}
	
	function fire(&$actor, &$retval, $args)
	{
		$tar = $actor;

		if($args[0])
		{
			$tar = $this->ci->actor->getInfo($args[0]);
		
			if($tar['map'] != $actor['map'] || $tar['x'] != $actor['x']
				|| $tar['y'] != $actor['y'] || $tar['indoors'] != $actor['indoors'])
			{
				return array("They are not here.");
			}
		}

		$i = $args[1];
		$ins = $this->ci->actor->getInstanceOf($i, $actor['actor'], 1, true);
		if(! $ins) return array("You can't use what you don't have.");
		$item = $this->ci->item->getInfo($ins);
		if(! $item) return array("Unknown item: {$i}.");
		if(! $item['target']) return array("That item is unusable.");
		$which = "i_{$item['abbrev']}";
		$this->ci->load->model("items/{$which}");
		return $this->ci->$which->fire($item, $actor, $tar);
	}
	
	function params(&$actor, $target = false)
	{
		$res = $this->ci->actor->getItems($actor['actor']);
		$p = array();

		foreach($res as $r)
		{
			if($r['target'] == null)
				continue;
			if(($target && $r['target'] > 0) || (! $target && $r['target'] < 2))
				$p[] = array($r['inum'], $r['iname']);
		}

		return $p;
	}
	
	
	function show(&$actor, &$victim)
	{
		if($victim['actor'] <= 0) return false;
		if($this->params($actor, $victim))
			return true;
		return false;
	}
}
