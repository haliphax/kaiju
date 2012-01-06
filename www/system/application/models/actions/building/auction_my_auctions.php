<?php if(! defined('BASEPATH')) exit();

class auction_my_auctions extends Model
{
	private $ci;
	
	function auction_my_auctions()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function fire(&$actor, &$retval, $args)
	{
		$this->ci->load->model('map');
		$this->ci->load->model('actor');
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'], $actor['y']);
		$bldg = $this->ci->map->buildingInfo($actor['map'], $cell['building']);
		$whichmap = ($bldg['owner'] == 0 ? 0 : $actor['map']);
		$whichbldg = ($bldg['owner'] == 0 ? 0 : $cell['building']);

		# get auction info and total number of bids
		$s = <<<SQL
			select s.auction, price, time_left, count(b.actor) as bids
			from auction_sale s
			left join auction_bid b on s.auction = b.auction
			where s.actor = ? and s.map = ? and s.building = ?
			group by auction
			order by time_left asc
SQL;
		$q = $this->db->query($s, array($actor['actor'], $whichmap, $whichbldg));
		$r = $q->result_array();
		$auctions = array();
		$this->ci->load->model('action');
		
		foreach($r as $row) {
			# get actor's auctions
			$auctions[$row['auction']] = array(
				'items'		=> array(),
				'price'		=> $row['price'],
				'bids' 		=> $row['bids'],
				'timeleft'	=> $row['time_left'],
				);
			
			$rr = $this->ci->action->_getAuctionItems($row['auction']);
			foreach($rr as $rrow)
				$auctions[$row['auction']]['items'][] = $rrow;
		}
		
		$retval['auctions'] = $auctions;
	}
}
