<?php if(! defined('BASEPATH')) exit();

class auction_auction_accept extends NoCacheModel
{
	private $ci;
	
	function auction_auction_accept()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function fire(&$actor, &$retval, $args)
	{
		# TODO: impose bid limits & item limits
		$q = $this->db->query(
			'select 1 from auction_sale where auction = ? and actor = ?',
			array($args[0], $actor['actor']));
		if($q->num_rows() < 1)
			return array("You do not have such an auction on record.");
		$q = $this->db->query(
			'select 1 from auction_bid where auction = ? and actor = ?',
			array($args[0], $args[1])
			);
		if($q->num_rows() < 1) return array("There is no such bid.");
		# transfer ownership of bid items to auctioneer
		$s = <<<SQL
			update actor_item ai, auction_bid ab, auction_bid_item abi
			set ai.actor = ?
			where ab.actor = ? and ab.auction = ?
				and ab.auction = abi.auction and ai.instance = abi.instance
SQL;
		$this->db->query($s, array($actor['actor'], $args[1], $args[0]));
		if($this->db->affected_rows() < 1)
			return array("Error transferring items to auctioneer.");
		# transfer ownership of auction items to bidder
		$s = <<<SQL
			update actor_item ai, auction_sale_item asi
			set ai.actor = ?
			where asi.auction = ? and ai.instance = asi.instance
SQL;
		$this->db->query($s, array($args[1], $args[0]));
		if($this->db->affected_rows() < 1)
			return array("Error transferring items to bidder.");
		$this->ci->load->model('action');
		$items = $this->ci->action->_getAuctionItems($args[0]);
		$itemstr = $this->ci->action->_getItemString($items);
		$this->ci->load->model('actor');
		$this->ci->actor->sendEvent(
			"You won an auction; items have been transferred. ({$itemstr})",
			$args[1]
			);
		$s = 'select actor from auction_bid where auction = ? and actor != ?';
		$q = $this->db->query($s, array($args[0], $args[1]));
		$r = $q->result_array();
		$actors = array();
		foreach($r as $row) $actors[] = $row['actor'];
		$this->ci->actor->sendEvent(
			"An auction you were bidding on was won by someone else. ("
				. $itemstr . ")",
			$actors
			);
		# close the auction and give other bidders back their items
		$this->ci->action->_closeAuction($args[0]);
		return array("Bid accepted; ownership transferred.");
	}
}