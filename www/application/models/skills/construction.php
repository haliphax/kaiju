<?php if(! defined('BASEPATH')) exit();

class construction extends CI_Model
{
	private $ci;
	private $cost;
	
	# constructor
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
		$this->ci->load->model('skills');
		$this->cost = $this->ci->skills->getCost('construction');
	}

	# use skill
	function fire(&$actor, $args)
	{
		if($actor['stat_ap'] < $this->cost['cost_ap'])
			return $this->ci->skills->noap;
		# tile's not under construction
		if(! $this->show($actor))
			return; # array("You can't do that here.");
		$instance = $this->ci->actor->getInstanceOf($args[0], $actor['actor']);
		if($instance === false) return; # ("You don't have any of those.");
		$this->ci->load->model('map');
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$s = <<<SQL
			select amt from building_progress
			where inum = ? and map = ? and building = ?
SQL;
		$q = $this->db->query($s, array($args[0], $actor['map'],
			$cell['building']));
		if($q->num_rows() <= 0) return; # array("That isn't necessary.");
		$r = $q->row_array();
		$msg = array("You construct.");
		
		if($r['amt'] <= 1)
		{
			$s = <<<SQL
				delete from building_progress
				where map = ? and building = ? and inum = ?
SQL;
			$this->db->query($s, array($actor['map'], $cell['building'],
				$args[0]));
		}
		else
		{
			$s = <<<SQL
				update building_progress set amt = amt - 1
				where map = ? and building = ? and inum = ?
SQL;
			$this->db->query($s, array($actor['map'], $cell['building'],
				$args[0]));
		}
		
		if($this->db->affected_rows() <= 0) return array("Construction error");
		$s = 'delete from actor_item where actor = ? and instance = ?';
		$this->db->query($s, array($actor['actor'], $instance));
		
		if($r['amt'] <= 1)
		{
			$s = <<<SQL
				select 1 from building_progress where map = ? and building = ?
SQL;
			$q = $this->db->query($s, array($actor['map'], $cell['building']));
			
			if($q->num_rows() <= 0)
			{
				$this->ci->actor->addXP($actor, 1);
				$msg[] = "The building is finished! You stand back for a moment and marvel at your hard work.";
				$this->ci->actor->setMapFlag($actor['actor']);
				$s = <<<SQL
					update map_cell
					set tile = (
						select def_tile from structure s
						join building b on s.structure = b.structure
						where b.map = ? and b.building = ?
						)
					where map = ? and building = ?
SQL;
				$this->db->query($s, array($actor['map'], $cell['building'],
					$actor['map'], $cell['building']));
				$s = <<<SQL
					update building
					set descr = (
						select descr from structure
						where structure = building.structure
						), hp = 120
					where map = ? and building = ?
SQL;
				$this->db->query($s, array($actor['map'], $cell['building'],
					$actor['map'], $cell['building']));
			}
		}
		
		$ret = $this->ci->actor->spendAP($this->cost['cost_ap'], &$actor);
		foreach($ret as $r) $msg[] = $r;
		$this->ci->actor->addXP($actor, 1);
		return $msg;
	}
	
	function show(&$actor)
	{
		$this->ci->load->model('map');
		# get cell info to determine building # (if any)
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		
		if($this->ci->map->tileIsUnderConstruction($actor['map'],
			$cell['building']))
		{
			return true;
		}
		
		return false;
	}
	
	function params(&$actor)
	{
		$this->ci->load->model('map');	
		$actor = $this->ci->actor->getInfo($actor['actor']);
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$s = <<<SQL
			select iname, ai.inum, count(ai.inum) as num from actor_item ai
			join building_progress bp on ai.inum = bp.inum
			join item i on bp.inum = i.inum
			where bp.building = ? and ai.actor = ? and map = ?
			group by ai.inum
SQL;
		$q = $this->db->query($s, array($cell['building'], $actor['actor'],
			$actor['map']));
		if($q->num_rows() <= 0)
			return array(array("", "You lack the necessary items."));
		$r = $q->result_array();
		$ret = array();
		foreach($r as $row)
			$ret[] = array($row['inum'],
				$row['iname'] . ($row['num'] ? " [{$row['num']}]" : ''));
		return $ret;
	}	
}
