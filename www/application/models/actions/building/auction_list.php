<?php if(! defined('BASEPATH')) exit();

class auction_list extends CI_Model
{
	private $ci;
	
	function auction_list()
	{
		parent::__construct();
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

		# get auction info and total # of bids
		$s = <<<SQL
			select aname, a.actor, s.auction, price, time_left,
				count(b.actor) as bids,
				(case when ? in
					(select actor from auction_bid 
					where auction = s.auction)
				then 1 else 0 end) as mybid
			from auction_sale s
			left join auction_bid b on s.auction = b.auction
			join actor a on s.actor = a.actor
			where s.actor != ? and time_left > 0 and s.map = ? and s.building = ?
			group by auction
			order by time_left asc
SQL;
		$q = $this->db->query($s, array($actor['actor'], $actor['actor'], $whichmap, $whichbldg));
		$r = $q->result_array();
		$auctions = array();
		
		# get items in auction
		foreach($r as $row)
		{
			$auction = array(
				'aname'		=> $row['aname'],
				'actor'		=> $row['actor'],
				'auction'	=> $row['auction'],
				'price'		=> $row['price'],
				'timeleft'	=> $row['time_left'],
				'bids'		=> $row['bids'],
				'mybid'		=> $row['mybid'],
				'items'		=> array(),
				);
			$s = <<<SQL
				select iname, si.instance,
					(case when stack = 0 then ai.instance
					else -1 * ai.inum end) as sort,
					(case when stack = 0 then 1
					else count(ai.inum) end) as num
				from auction_sale_item si
				join actor_item ai on ai.instance = si.instance
				join item i on ai.inum = i.inum
				where auction = ?
				group by sort
				order by lower(iname) asc
SQL;
			$q = $this->db->query($s, array($row['auction']));
			$auction['items'] = $q->result_array();
			$auctions[] = $auction;
		}

		# somebody else's shop?
		if($bldg['owner'] != 0 && $bldg['owner'] != $actor['actor'])
		{
			$owner = $this->ci->actor->getInfo($bldg['owner']);
			if($owner['clan'] == 0 || $owner['clan'] != $actor['clan'])
				$retval['hidemyauctionsbutton'] = 1;
		}

		$retval['auctions'] = $auctions;
	}

	function show(&$actor)
	{
		if(! $actor['indoors'])
			return false;
		return true;
	}
}
