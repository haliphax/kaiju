<?php if(! defined('BASEPATH')) exit();

class action extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
#===============================================================================
# action-related methods
#===============================================================================

	# get global actions =======================================================
	function getGlobals(&$actor)
	{
		$sql = <<<SQL
			select abbrev, descr, cost, atype,
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
			from action where atype = 'global'
			order by lcase(descr) asc
SQL;
		$q = $this->db->query($sql);
		$r = $q->result_array();
		$actions = array();
		
		foreach($r as $row)
		{
			$which = $row['abbrev'];			
			$this->ci->load->model("actions/global/{$which}");
			$res = $this->ci->$which->show($actor);
			if($res !== false) $actions[] = $row;
		}
		
		return $actions;
	}
	
	# get actor actions ========================================================
	function getActors(&$actor, $victim)
	{
		$sql = <<<SQL
			select abbrev, descr, cost, atype,
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
			from action where atype = 'actor'
			order by lcase(descr) asc
SQL;
		$q = $this->db->query($sql);
		if($q->num_rows() > 0)
			$victim = $this->ci->actor->getInfo($victim);
		$r = $q->result_array();
		$actions = array();
		
		foreach($r as $row)
		{
			$which = $row['abbrev'];			
			$this->ci->load->model("actions/actor/{$which}");
			$res = $this->ci->$which->show($actor, $victim);
			if($res !== false) $actions[] = $row;
		}
		
		return $actions;
	}
	
	function getCellActions(&$actor)
	{
		$sql = <<<SQL
			select abbrev, descr, cost, atype,
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
			from action a
			join action_cell ac on a.action = ac.action
			where atype = 'cell' and map = ? and x = ? and y = ? and
				(indoors = ? or indoors = -1)
			
			union
			
			select abbrev, descr, cost, atype,
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
			from action a
			join action_cell_class acc on a.action = acc.action
				and acc.cclass in (
					select cclass from map_cell_class
					where map = ? and x = ? and y = ? and
						(indoors = ? or indoors = -1)
					
					union
					
					select tclass as cclass from map_cell mc
					join tile_class tc on mc.tile = tc.tile
					where map = ? and x = ? and y = ? and
						(indoors = ? or indoors = -1)
					)
			where atype = 'cell'
			order by lcase(descr) asc
SQL;
		$q = $this->db->query($sql, array($actor['map'], $actor['x'],
			$actor['y'], $actor['indoors'], $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors'], $actor['map'], $actor['x'],
			$actor['y'], $actor['indoors']));
		$r = $q->result_array();
		$actions = array();
		
		foreach($r as $row)
		{
			$which = $row['abbrev'];
			$this->ci->load->model("actions/cell/{$which}");
			$res = $this->ci->$which->show($actor, $victim);
			if($res !== false) $actions[] = $row;
		}
		
		return $actions;
	}
	
	# get dead actions =========================================================
	function getDead(&$actor)
	{
		$sql = <<<SQL
			select abbrev, descr, cost, atype,
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
			from action where atype = 'dead'
			order by lcase(descr) asc
SQL;
		$q = $this->db->query($sql);
		$r = $q->result_array();
		$actions = array();
		
		foreach($r as $row)
		{
			$which = $row['abbrev'];
			$this->ci->load->model("actions/dead/{$which}");
			$res = $this->ci->$which->show($actor);
			if($res !== false) $actions[] = $row;
		}
		
		return $actions;
	}
	
	# get actions for building =================================================
	function getBuilding(&$actor, $map, $b)
	{
		$sql = <<<SQL
			select atype, abbrev, descr, cost, 
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
			from action a join action_building b on a.action = b.action
			where atype = 'building' and map = ? and building = ?
				and (indoors = ? or indoors = -1)
			union

			select atype, abbrev, descr, cost, 
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
			from action_building_class abc join action a on a.action = abc.action
			where abc.bclass in
				(
					select bclass from building_class bc
					where bc.map = ? and bc.building = ?
					and (indoors = ? or indoors = -1)
				)
			union

			select atype, abbrev, descr, cost, 
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
			from action_structure s join action a on a.action = s.action
			where structure =
				(
					select structure from building bl
					where bl.map = ? and bl.building = ?
				)
				and (indoors = ? or indoors = -1)
			union
			
			select atype, abbrev, descr, cost, 
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
			from action_structure_class c join action a on a.action = c.action
			where c.sclass in
				(
					select sclass from structure_class sc
					where sc.structure =
						(
							select structure from building bl
							where bl.map = ? and bl.building = ?
						)
						and (indoors = ? or indoors = -1)
				)
			order by lcase(descr) asc
SQL;
		$q = $this->db->query($sql, array($map, $b, $actor['indoors'], $map, $b,
			$actor['indoors'], $map, $b, $actor['indoors'], $map, $b,
			$actor['indoors']));
		$r = $q->result_array();
		$actions = array();
		
		foreach($r as $row)
		{
			$which = $row['abbrev'];			
			$this->ci->load->model("actions/{$row['atype']}/{$which}");
			$res = $this->ci->$which->show($actor);
			if($res !== false) $actions[] = $row;
		}
		
		return $actions;
	}
	
	# is action repeatable? ====================================================
	function isRepeatable($type, $abbrev)
	{
		$s = <<<SQL
			select 1 from action
			where atype = ? and abbrev = ? and repeatable = b'1'
SQL;
		$q = $this->db->query($s, array($type, $abbrev));
		return ($q->num_rows() > 0);
	}
	
	# get action cost ==========================================================
	function getCost($type, $abbrev)
	{
		$type = preg_replace('#[^a-z]*#i', '', $type);
		$sql = "select cost from action where atype = ? and abbrev = ?";
		$q = $this->db->query($sql, array($type, $abbrev));
		$r = $q->row_array();
		return $r['cost'];
	}

	# get action abbrev ========================================================
	function getAbbrev($type, $action)
	{
		$sql = "select abbrev from action where atype = ? and action = ?";
		$q = $this->db->query($sql, array($type, $action));
		$r = $q->row_array();
		return $r['abbrev'];
	}
	
	# get action information ===================================================
	function getInfo($type, $action)
	{
		$sql = "select descr from action where atype = ? and abbrev = ?";
		$q = $this->db->query($sql, array($type, $action));
		$r = $q->row_array();
		return $r;
	}
	
	# get action parameters ====================================================
	function getParameters($type, $action, $actor)
	{
		$this->ci->load->model("actions/{$type}/{$action}");
		return $this->ci->$action->params($actor);
	}
	
#===============================================================================
# re-usable functions
#===============================================================================
	
	# targettable body parts ===================================================
	function bodyparts()
	{
		return array(
			array('Head', 'Head'),
			array('Torso', 'Torso'),
			array('Arms', 'Arms'),
			array('Legs', 'Legs')
			);
	}

	# too far away? ============================================================
	function canMelee($v, $a, $d)
	{
		if($this->ci->actor->isElevated($a) != $this->ci->actor->isElevated($v)
			&& $d == 'melee')
		{
			return false;
		}
		
		return true;
	}	


	# enter/exit helper ========================================================
	function indoors(&$actor, &$succ, $i = 1)
	{
		$this->ci->actor->setLast($actor['actor']);
		
		if($actor['stat_ap'] <= 0 || $actor['stat_hp'] <= 0
			|| $actor['indoors'] == $i)
		{
			return false;
		}
		
		if($this->ci->actor->getEncumbrance($actor['actor']) > 60)
			return array("You are too encumbered to move.");
		if($this->ci->actor->isElevated($actor['actor']))
			return array("You must descend before you may enter.");
		$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'],
			$actor['y'], false);
		$msg = array();
		$ret = $this->ci->actor->setIndoors($i, $actor['actor']);
		
		if($ret !== false)
		{
			$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'],
				$actor['y'], $i);
			$this->ci->actor->setStatFlag($actor['actor']);
			foreach($ret as $r) $msg[] = $r;
			$rret = $this->ci->actor->spendAP(1, $actor);
			foreach($rret as $r) $msg[] = $r;
		}
		
		return $msg;
	}
	
	# auction helpers ==========================================================
	
	# close an auction, whether by accepting a bid or cancelling ---------------
	function _closeAuction($auc)
	{
		$this->returnOwnership($auc);
		$s = 'delete from auction_sale_item where auction = ?';		
		$this->db->query($s, array($auc));
		$s = 'delete from auction_sale where auction = ?';
		$this->db->query($s, array($auc));
		$s = 'delete from auction_bid_item where auction = ?';
		$this->db->query($s, array($auc));
		$s = 'delete from auction_bid where auction = ?';
		$this->db->query($s, array($auc));	
	}
	
	# return ownership of items involved in an auction -------------------------
	function returnOwnership($auc)
	{
		$s = <<<SQL
			update actor_item ai, auction_bid_item abi
			set ai.actor = abi.actor
			where abi.auction = ? and ai.instance = abi.instance
				and ai.actor = 0
SQL;
		$this->db->query($s, array($auc));
		$s = <<<SQL
			update actor_item ai, auction_sale_item asi, auction_sale aus
			set ai.actor = aus.actor
			where aus.auction = ? and aus.auction = asi.auction
				and asi.instance = ai.instance
				and ai.actor = 0
SQL;
		$this->db->query($s, array($auc));
	}
	
	# get items for an auction -------------------------------------------------
	function _getAuctionItems($auc)
	{
		$s = <<<SQL
			select asi.instance, iname,
				(case when stack = 0 then ai.instance
				else -1 * ai.inum end) as sort,
				(case when stack = 0 then 1
				else count(ai.inum) end) as num			
			from auction_sale_item asi
			join actor_item ai on ai.instance = asi.instance
			join item i on i.inum = ai.inum
			where asi.auction = ?
			group by sort
			order by lower(iname) asc
SQL;
		$q = $this->db->query($s, array($auc));
		return $q->result_array();
	}
	
	# get items for a bid ------------------------------------------------------
	function _getBidItems($auc, $bid)
	{
		$items = array();
		$s = <<<SQL
			select abi.instance, iname,
				(case when stack = 0 then ai.instance
				else -1 * ai.inum end) as sort,
				(case when stack = 0 then 1
				else count(ai.inum) end) as num			
			from auction_bid_item abi
			join actor_item ai on ai.instance = abi.instance
			join item i on i.inum = ai.inum
			where abi.auction = ? and abi.actor = ?
			group by sort
			order by lower(iname) asc
SQL;
		$q = $this->db->query($s, array($auc, $bid));
		return $q->result_array();
	}
	
	# get string representation of items ---------------------------------------
	function _getItemString($items)
	{
		$itemstr = '';
		foreach($items as $i)
			$itemstr .= ($itemstr ? ', ' : '')
				. "<a onclick='describeItem({$i['instance']});'>"
				. "{$i['iname']}" . ($i['num'] > 1 ? " [{$i['num']}]" : "")
				. "</a>";
		return $itemstr;
	}
	
	# too many bids already? ---------------------------------------------------
	function tooManyBids($actor)
	{
		$s = 'select count(1) as cnt from auction_bid where actor = ?';
		$q = $this->db->query($s, array($actor));
		$r = $q->row_array();
		if($r['cnt'] < 5) return false;
		return true;
	}
	
	# too many auctions already? -----------------------------------------------
	function tooManyAuctions($actor)
	{
		if(is_numeric($actor))
			$actor = $this->ci->actor->getInfo($actor);
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'], $actor['y']);
		$building = $this->ci->map->buildingInfo($actor['map'], $cell['building']);

		if($building['owner'] == $actor['actor'])
		{
			$s = 'select count(1) as cnt from auction_sale where actor = ? and map = ? and building = ?';
			$q = $this->db->query($s, array($actor['actor'], $actor['map'], $cell['building']));
			$r = $q->row_array();

			# Actor has 'Storeroom' skill - can list up to 10 auctions in their own stores
			if($this->ci->actor->hasSkill('storeroom', $actor['actor']))
			{
				if($r['cnt'] < 10)
					return false;
			}
			else if($r['cnt'] < 5)
				return false;
		}
		else
		{
			$s = 'select count(1) as cnt from auction_sale where actor = ?';
			$q = $this->db->query($s, array($actor['actor']));
			$r = $q->row_array();
		
			# Auctioneer (Improved) allows for 10 auctions instead of the normal 5
			if($this->ci->actor->hasSkill('auctioneer_imp', $actor['actor']))
			{
				if($r['cnt'] < 10)
					return false;
			}
			else if($r['cnt'] < 5)
				return false;
		}

		return true;
	}
}
