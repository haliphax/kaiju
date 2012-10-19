<?php if(! defined('BASEPATH')) exit();

class t60 extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function fire()
	{
		# TODO: bid/auction cancellation -> refund timers (wait 24h)
		# auction countdown/expiration			
		$this->load->model('action');
		$this->db->query(
			'update auction_sale set time_left = time_left - 1');
		$q = $this->db->query(
			'select auction, actor from auction_sale where time_left = 0');
		$r = $q->result_array();
		
		foreach($r as $row) {
			$items = $this->action->_getItemString(
				$this->action->_getAuctionItems($row['auction']));
			$this->actor->sendEvent(
				"Your auction has closed, and will expire in 24 hours. ("
					. $items . ")",
				$row['actor']
				);
		}
		
		$q = $this->db->query(
			'select auction, actor from auction_sale where time_left <= -24'
			);
		$r = $q->result_array();
		
		foreach($r as $row) {
			$q = $this->db->query(
				'select actor from auction_bid where auction = ?',
				array($row['auction'])
				);
			$actors = array();
			$rr = $q->result_array();
			foreach($rr as $rrow) $actors[] = $rrow['actor'];
			$items = $this->action->_getItemString(
				$this->action->_getAuctionItems($row['auction']));
			$this->actor->sendEvent(
				"An auction you were bidding on has expired. ({$items})",
				$actors
				);
			$this->actor->sendEvent("Your auction has expired. ({$items})",
				$row['actor']);
			$this->action->_closeAuction($row['auction']);
					echo "Closing auction {$row['auction']}\n";
		}
	}
}
