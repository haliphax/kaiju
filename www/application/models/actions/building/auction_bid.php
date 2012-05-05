<?php if(! defined('BASEPATH')) exit();

class auction_bid extends CI_Model
{
	private $ci;
	
	function auction_bid()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function fire(&$actor, &$retval, $args)
	{
		$this->ci->load->model('action');
		if($this->ci->action->tooManyBids($actor['actor']))
			return array("You've made enough bids already, pal.");
		if(count($args) < 2)
			return array("You might want to select at least ONE item.");
		$ss = <<<SQL
			insert into auction_bid_item (auction, actor, instance)
			values (?, ?, ?)
SQL;
		$n = count($args);
		if($n > 5) return array("That is too many items.");
		
		# make sure they have the items
		for($a = 1; $a < $n; $a++) {
			$pos = strpos($args[$a], '_');
			
			# item is stacked, pass additional parameters
			if($pos !== false) {
				$lim = preg_replace('#[^0-9]#', '',
					substr($args[$a], $pos + 1));
				$item = substr($args[$a], 0, $pos);
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
				$n = count($args);
				continue;
			} else {				
				$q = $this->db->query(
					'select 1 from actor_item where actor = ? and instance = ?',
					array($actor['actor'], $args[$a])
					);
				if($q->num_rows() < 1)
					return
						array("You do not have that item in your possession.");
				$this->db->query($ss, array($args[0], $actor['actor'], $args[$a]));
				
				if($this->db->affected_rows() < 1) {
					$sql = <<<SQL
						update actor_item set actor = ?
						where actor = 0 and instance in (
							select instance from auction_bid_item abi
							where auction = ? and abi.actor = ?
							)
SQL;
					$this->db->query($sql,
						array($actor['actor'], $args[0], $actor['actor'])
						);					
					$this->db->query(
						'delete from auction_bid_item where auction = ? and actor = ?',
						array($args[0], $actor['actor'])
						);
					return array("Error bidding item(s).");
				}
				
				$this->db->query(
					'update actor_item set actor = 0 where instance = ?',
					array($a)
					);
			}
		}
		
		$s = 'insert ignore into auction_bid (auction, actor) values (?, ?)';
		$this->db->query($s, array($args[0], $actor['actor']));
		$q = $this->db->query(
			'select actor from auction_sale where auction = ?',
			array($args[0])
			);
		$r = $q->row_array();
		$this->ci->load->model('actor');
		$this->ci->actor->sendEvent(
			"Someone has bid on your auction. ("
				. $this->ci->action->_getItemString(
					$this->ci->action->_getAuctionItems($args[0])) . ")",
			$r['actor']);
		return array("Bid processed.");
	}
	
	function params(&$actor)
	{
		$who = $actor;
		if (is_array($actor)) $who = $actor['actor'];
		
		$s = <<<SQL
			select iname, instance, i.durability, ai.durability as durmax,
				(case when stack = 0 then ai.instance
				else -1 * ai.inum end) as sort,
				(case when stack = 0 then 1
				else count(ai.inum) end) as num
			from actor_item ai
			join item i on ai.inum = i.inum
			where ai.actor = ? and eq_slot is null
			group by sort
			order by lower(iname) asc
SQL;
		$q = $this->db->query($s, array($who));
		return $q->result_array();
	}
}