<?php if(! defined('BASEPATH')) exit();

class t60 extends CI_Model
{
	private $ci;

	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('action');
		$this->ci->load->model('actor');
	}

	function fire()
	{
		# TODO: bid/auction cancellation -> refund timers (wait 24h)
		# auction countdown/expiration			
		$this->db->query(
			'update auction_sale set time_left = time_left - 1');
		$q = $this->db->query(
			'select auction, actor from auction_sale where time_left = 0');
		$r = $q->result_array();
		
		foreach($r as $row) {
			$items = $this->ci->action->_getItemString(
				$this->ci->action->_getAuctionItems($row['auction']));
			$this->ci->actor->sendEvent(
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
			$items = $this->ci->action->_getItemString(
				$this->ci->action->_getAuctionItems($row['auction']));
			$this->ci->actor->sendEvent(
				"An auction you were bidding on has expired. ({$items})",
				$actors
				);
			$this->ci->actor->sendEvent("Your auction has expired. ({$items})",
				$row['actor']);
			$this->ci->action->_closeAuction($row['auction']);
					echo "Closing auction {$row['auction']}\n";
		}
	}
}
