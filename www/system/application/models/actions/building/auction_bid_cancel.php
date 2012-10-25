<?php if(! defined('BASEPATH')) exit();

class auction_bid_cancel extends Model
{
	private $ci;
	
	function auction_bid_cancel()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function fire(&$actor, &$retval, $args)
	{
		$s = <<<SQL
			update actor_item set actor = ? where instance in
				(select instance from auction_bid_item
				where auction = ? and actor = ?)
SQL;
		$this->db->query($s, array($actor['actor'], $args[0], $actor['actor']));
		$s = 'delete from auction_bid_item where auction = ? and actor = ?';
		$this->db->query($s, array($args[0], $actor['actor']));
		$s = 'delete from auction_bid where auction = ? and actor = ?';
		$this->db->query($s, array($args[0], $actor['actor']));
		return array("Your bid has been withdrawn.");
	}
}