<?php if(! defined('BASEPATH')) exit();

class i_tessen extends CI_Model
{
	private $ci;
	private $e;
	
	function i_tessen()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('pdata');
		$q = $this->db->query(
			"select effect from effect where abbrev = 'defense_ranged'");
		$r = $q->row_array();
		$this->e = $r['effect'];
	}
	
	function equip(&$actor, &$instance)
	{
		if(! $this->ci->actor->hasClass('samurai', $actor['actor']))
		{
			$instance = 0;
			return array(
				"You do not understand how to wield this item.");
		}
		
		if($this->ci->actor->hasEffect('defense_ranged', $actor['actor']))
			$this->ci->pdata->inc('effect', $this->e, 1, $actor['actor']);
		else
		{
			$msg = array();
			$ret = $this->ci->actor->addEffect('defense_ranged', $actor);
			foreach($ret as $r) $msg[] = $r;
			$this->ci->pdata->set('effect', $this->e, 1, $actor['actor']);
			return $msg;
		}
	}
	
	function remove(&$actor, &$instance)
	{
		$p = $this->ci->pdata->get('effect', $this->e, $actor['actor']);
		
		if($p > 1)
			$this->ci->pdata->inc('effect', $this->e, -1, $actor['actor']);
		else
		{
			$this->ci->pdata->clear('effect', $this->e, $actor['actor']);
			$msg = array();
			$ret = $this->ci->actor->removeEffect('defense_ranged', $actor);
			foreach($ret as $r) $msg[] = $r;
			return $msg;		
		}		
	}
}