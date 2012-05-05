<?php if(! defined('BASEPATH')) exit();

class e_exertion extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('pdata');
	}
	
	function on(&$actor)
	{
		$this->ci->pdata->set('effect', 'exertion', 0, $actor['actor']);
	}
	
	function off(&$actor)
	{
		$this->ci->pdata->clear('effect', 'exertion', $actor['actor']);
	}
	
	function disp(&$actor)
	{
		$cnt = '??';
		
		try {
			$cnt = $this->ci->pdata->get('effect', 'exertion', $actor['actor']);
		} catch(Exception $e) {}
		
		return "Exertion: {$cnt}";
	}
	
	function tick()
	{
		$this->ci->load->model('effects');
		$res = $this->ci->effects->getActorsWith('exertion');
		if(! $res) return false;
		$ret = array();
		$actors = array();
		
		foreach($res as $r)
		{
			$actors[] = $r['actor'];
			$ret[] = "{$r['actor']} - Exertion";
			$cnt = $this->ci->pdata->get('effect', 'exertion', $r['actor']);
			
			if($r['tile'] == 3)
			{
				$this->ci->pdata->set('effect', 'exertion', ++$cnt,
					$r['actor']);
				$this->ci->actor->setStatFlag($r['actor']);
				
				if($cnt > 16)
				{
					$ret[] = "{$r['actor']} - Drowned";
					$this->ci->actor->sendEvent("You drowned.", $r['actor']);
					$this->ci->actor->setStat('hp', 0, $r['actor']);
					$this->ci->actor->setStatFlag($r['actor']);
					$this->ci->pdata->pdata->set('effect', 'exertion', 0,
						$r['actor']);
				}
			}
			else
			{
				$this->ci->pdata->set('effect', 'exertion', --$cnt,
					$r['actor']);
				
				if($cnt <= 0)
				{
					$this->ci->actor->removeEffect('exertion', $r);
				}
			}
			
			$this->ci->actor->setStatFlag($r['actor']);
		}
		
		return $ret;
	}
}