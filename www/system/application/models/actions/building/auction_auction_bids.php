<?php if(! defined('BASEPATH')) exit();

class auction_auction_bids extends Model
{
	private $ci;
	
	function auction_auction_bids()
	{
		parent::Model();
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
		# get bids for auction
		$s = 'select actor from auction_bid where auction = ?';
		$q = $this->db->query($s, array($args[0]));
		$r = $q->result_array();
		$bids = array();		
		$this->ci->load->model('action');
		foreach($r as $row)
			$bids[$row['actor']] =
				$this->ci->action->_getBidItems($args[0], $row['actor']);		
		$retval['bids'] = $bids;
	}
}