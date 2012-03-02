<?php if(! defined('BASEPATH')) exit();

class i_tessen extends CI_Model
{
	private $ci;
	
	function i_tessen()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	function equip(&$actor, &$instance)
	{
		if(! $this->ci->actor->hasClass('samurai', $actor['actor']))
		{
			$instance = 0;
			return array(
				"You do not understand how to wield this item."
				);
		}
		
		if($this->ci->actor->hasEffect('defense_ranged', $actor['actor']))
		{
			$e = $this->myeffect();
			$this->ci->load->model('pdata');
			$this->ci->pdata->set('effect', $e, 1, $actor['actor']);
		}
		else
		{
			$msg = array();
			$ret = $this->ci->actor->addEffect('defense_ranged', $actor);
			foreach($ret as $r) $msg[] = $r;
			return $msg;
		}
	}
	
	function remove(&$actor, &$instance)
	{
		$this->ci->load->model('pdata');
		$e = $this->myeffect();
		$p = $this->ci->pdata->get('effect', $e, $actor['actor']);
		
		if($p)
			$this->ci->pdata->clear('effect', $e, $actor['actor']);
		else
		{
			$msg = array();
			$ret = $this->ci->actor->removeEffect('defense_ranged', $actor);
			foreach($ret as $r) $msg[] = $r;
			return $msg;		
		}		
	}
	
	private function myeffect()
	{
		$q = $this->db->query(
			"select effect from effect where abbrev = 'defense_ranged'");
		$r = $q->row_array();
		return $r['effect'];
	}
}