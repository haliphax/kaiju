<?php if(! defined('BASEPATH')) exit();

class shopkeeping extends SkillModel
{

	function __construct()
	{
		parent::__construct();
		$this->ci->load->model('map');
		$this->cost = $this->ci->skills->getCost('shopkeeping');
	}

	function fire(&$actor)
	{
		if(! $this->show($actor))
			return;
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->ci->skills->noap;
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$msg = array();
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], $actor);
		foreach($ret as $r) $msg[] = $r;

		if($this->ci->map->buildingHasClass($actor['map'], $cell['building'], 'shop') !== false)
		{
			$this->ci->map->removeBuildingClass($actor['map'], $cell['building'], 'shop');
			$s = 'select auction from auction_sale where map = ? and building = ?';
			$q = $this->db->query($s, array($actor['map'], $cell['building']));
			$r = $q->result_array();
			$this->ci->load->model('actions/building/auction_auction_cancel');
			foreach($r as $row)
				$msg[] = $this->ci->auction_auction_cancel->fire($actor, $retval, array($row['auction']));
			$msg[] = "You close down your shop.";
			return $msg;
		}
		else
		{
			$this->ci->map->addBuildingClass($actor['map'], $cell['building'], 'shop');
			$msg[] = "You set up shop.";
		}

		return $msg;
	}

	function show(&$actor)
	{
		if(! $actor['indoors'])
			return false;
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$bldg = $this->ci->map->buildingInfo($actor['map'],
			$cell['building']);
		# do we own the building?
		if($bldg['owner'] !== $actor['actor'])
			return false;

		# do we already have as many shops as we can have?
		if($this->ci->map->buildingHasClass($actor['map'], $cell['building'], 'shop'))
		{
			$s = <<<SQL
				select count(1) as cnt
				from building b
				join building_class bc on b.building = bc.building
				where owner = ? and bclass in (
					select sclass from class_structure where abbrev = 'shop'
				)
SQL;
			$q = $this->db->query($s, $actor['actor']);
			$r = $q->row_array();

			# user has 'Corporation' skill -- can have 3 shops
			if($this->ci->actor->hasSkill('corporation', $actor['actor']))
			{
				if($r['cnt'] >= 3)
					return false;
			}
			# user has 'Franchise' skill - can have 2 shops
			else if($this->ci->actor->hasSkill('franchise', $actor['actor']))
			{
				if($r['cnt'] >= 2)
					return false;
			}
			# user only has 'Shopkeeping' skill - can have 1 shop
			else
			{
				if($r['cnt'] >= 1)
					return false;
			}
		}

		if($this->ci->map->tileIsUnderConstruction($actor['map'],
			$cell['building']))
		{
			return false;
		}

		return true;
	}
}
