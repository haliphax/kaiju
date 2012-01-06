<?php if(! defined('BASEPATH')) exit();

class auction_auction extends Model
{
	private $ci;
	
	function auction_auction()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function fire(&$actor, &$retval, $args)
	{
		$this->ci->load->model('action');
		$this->ci->load->model('actor');

		if($this->ci->action->tooManyAuctions($actor['actor']))
			return array("You have enough auctions already, pal.");
		$n = count($args);
		if($n == 0)
			return array("You might want to select at least ONE item.");
		
		if($this->ci->actor->hasSkill("auctioneer", $actor['actor']))
		{
			if($n - 1 >= 10)
				return array("That is too many items.");
		}
		else if($n - 1 >= 5)
			return array("That is too many items.");
		
		# make sure they have the items
		foreach($args as $a) {
			$pos = strpos($a, '_');
			
			# item is stacked, pass additional parameters
			if($pos !== false) {
				$lim = preg_replace('#[^0-9]#', '', substr($a, $pos + 1));
				$item = substr($a, 0, $pos);
				$q = $this->db->query(
					'select inum from actor_item where instance = ?',
					array($item)
					);
				$r = $q->row_array();
				$s = <<<SQL
					select instance from actor_item where inum = ? and actor = ?
					limit {$lim}
SQL;
				$q = $this->db->query($s,
					array($r['inum'], $actor['actor']));
				if($q->num_rows() < $lim)
					return array("You don't have that many of that item.");
				$r = $q->result_array();
				foreach($r as $row) $args[] = $row['instance'];
				continue;
			} else {				
				$q = $this->db->query(
					'select 1 from actor_item where actor = ? and instance = ?',
					array($actor['actor'], $a)
					);
				if($q->num_rows() < 1)
					return
						array("You do not have that item in your possession.");
			}
		}
		
		$this->ci->load->model('map');
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'], $actor['y']);
		$bldg = $this->ci->map->buildingInfo($actor['map'], $cell['building']);

		# make sure they aren't trying to sell in someone else's shop
		if($bldg['owner'] != 0 && $bldg['owner'] != $actor['actor'])
		{
			$owner = $this->ci->actor->getInfo($bldg['owner']);
			if(! $owner['clan'] || $owner['clan'] != $actor['clan'])
				return array("This is not your shop; nor is it your clan's.");
		}

		$whichmap = ($bldg['owner'] == 0 ? 0 : $actor['map']);
		$whichbldg = ($bldg['owner'] == 0 ? 0 : $cell['building']);

		$this->db->query(
			'insert into auction_sale (map, building, actor, price, time_left) values (?, ?, ?, ?, 72)',
			array($whichmap, $whichbldg, $actor['actor'], $this->ci->input->post('price'))
			);
		if($this->db->affected_rows() < 1)
			return array("Error creating auction.");
		$rowid = $this->db->insert_id();
		$s = <<<SQL
			insert into auction_sale_item (auction, instance)
			values (?, ?)
SQL;
		
		# insert new items into auction_sale_item
		foreach($args as $a) {
			$pos = strpos($a, '_');
			if($pos !== false) continue;
			$this->db->query($s, array($rowid, $a));
			if($this->db->affected_rows() < 1)
				return array("Error associating item with auction.");
			# change ownership of auctioned items to actor 0 (the system ID)
			$this->db->query(
				'update actor_item set actor = 0 where instance = ?',
				array($a)
				);
			if($this->db->affected_rows() < 1)
				return array("Error removing ownership of item.");
		}
		
		return array("Auction processed.");
	}
	
	function params(&$actor)
	{
		$this->ci->load->model('actions/building/auction_bid');
		return $this->ci->auction_bid->params($actor);
	}
}
