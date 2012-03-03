<?php if(! defined('BASEPATH')) exit();

class auction_auction_cancel extends NoCacheModel
{
	private $ci;
	
	function auction_auction_cancel()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function fire(&$actor, &$retval, $args)
	{
		$q = $this->db->query(
			'select 1 from auction_sale where auction = ? and actor = ?',
			array($args[0], $actor['actor']));
		if($q->num_rows() < 1)
			return array("You do not have such an auction on record.");
		$this->ci->load->model('action');
		$items = $this->ci->action->_getAuctionItems($args[0]);
		$itemstr = $this->ci->action->_getItemString($items);
		$s = 'select actor from auction_bid where auction = ?';
		$q = $this->db->query($s, array($args[0]));
		$r = $q->result_array();
		$actors = array();
		foreach($r as $row) $actors[] = $row['actor'];
		$this->ci->load->model('actor');
		$this->ci->actor->sendEvent(
			"An auction you were bidding on has been withdrawn. ({$itemstr})",
			$actors
			);
		$this->ci->action->_closeAuction($args[0]);
		return array("Your auction has been withdrawn.");	
	}
}