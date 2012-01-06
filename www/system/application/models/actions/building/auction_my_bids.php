<?php if(! defined('BASEPATH')) exit();

class auction_my_bids extends Model
{
	private $ci;
	
	function auction_my_bids()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function fire(&$actor, &$retval, $args)
	{
		# get auction info and total number of bids
		$s = <<<SQL
			select s.auction, a.aname, price, time_left, count(b.actor) as bids
				from auction_sale s
			join auction_bid b on s.auction = b.auction
			join actor a on s.actor = a.actor
			where b.auction in (select auction from auction_bid where actor = ?)
			group by auction
			order by time_left asc
SQL;
		$q = $this->db->query($s, array($actor['actor']));
		$r = $q->result_array();
		$bids = array();
		$this->ci->load->model('action');
		
		foreach($r as $row)
		{
			# get items actor bid
			$bids[$row['auction']] = array(
				'auction'	=> $row['auction'],
				'items'		=> $this->ci->action->_getAuctionItems(
					$row['auction']),
				'bids' 		=> $row['bids'],
				'timeleft'	=> $row['time_left'],
				'mybid'		=> $this->ci->action->_getBidItems(
					$row['auction'], $actor['actor'])
				);
		}
		
		$retval['bids'] = $bids;
	}
}