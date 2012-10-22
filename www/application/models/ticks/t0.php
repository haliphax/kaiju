<?php if(! defined('BASEPATH')) exit();

class t0 extends CI_Model
{
	private $ci;

	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
		$this->ci->load->model('actor');
		$this->ci->load->model('map');
	}

	function fire()
	{
		# clear out old events
		$sql = <<<SQL
			delete from event_thread where event not in
			(select event from event where stamp > ?)
SQL;
		$this->db->query($sql, time() - (86400 * 14));
		# clear out read events
		$sql = <<<SQL
			delete from event where event not in
			(select event from event_thread)
SQL;
		$this->db->query($sql);
		# building damage
		$this->db->query('update building set hp = hp - ?', rand(5,10));
		# send notices to owners of buildings near collapse
		$s = <<<SQL
			select (case when b.descr is not null then b.descr
				when s.descr is not null then s.descr
				when c.descr is not null then c.descr
				when t.descr is not null then t.descr
				end) as descr, b.map, x, y, owner
			from building b
			join map_cell c on c.map = b.map and c.building = b.building
			left join structure s on s.structure = b.structure
			left join tile t on t.tile = c.tile
			where hp <= 20 and hp > 0
SQL;
		$q = $this->db->query($s);
		$r = $q->result_array();			
		foreach($r as $row)
			if($row['owner'])
				$this->ci->actor->sendEvent(
					"Your building ({$row['descr']} [{$row['x']},{$row['y']}]) is near collapse.",
					$row['owner']
					);			
		# clear away destroyed buildings
		$s = <<<SQL
			select (case when b.descr is not null then b.descr
				when s.descr is not null then s.descr
				when c.descr is not null then c.descr
				when t.descr is not null then t.descr
				end) as descr, b.map, x, y, owner, clan
			from building b
			join map_cell c on c.map = b.map and c.building = b.building
			left join structure s on s.structure = b.structure
			left join tile t on t.tile = c.tile
			left join clan_stronghold cs on cs.map = b.map
				and cs.building = b.building
			where hp <= 0
SQL;
		$q = $this->db->query($s);
		$r = $q->result_array();
		
		foreach($r as $row) {
			if($row['owner'])
				$this->ci->actor->sendEvent(
					"Your building ({$row['descr']} [{$row['x']},{$row['y']}]) has collapsed.",
					$row['owner']
					);
			$this->ci->map->sendCellEvent(
				"The building you were in has collapsed!", false,
				$row['map'], $row['x'], $row['y'], 1
				);
		}
		
		$s = <<<SQL
			update map_cell mc
			join building b on mc.map = b.map and mc.building = b.building
			set mc.building = NULL, mc.tile = mc.base_tile
			where hp <= 0
SQL;
		$this->db->query($s);
		/* remove stray building classes, progress tables, and search odds
		 * along with the building itself */
		$s = <<<SQL
			delete b, bc, bp, bs, cs
			from building b
			left join map_cell c on c.map = b.map
				and c.building = b.building
			left join building_class bc on bc.map = b.map
				and bc.building = b.building
			left join building_progress bp on bp.map = b.map
				and bp.building = b.building
			left join building_search bs on bs.map = b.map
				and bs.building = b.building
			left join clan_stronghold cs on cs.map = b.map
				and cs.building = b.building
			where x is null
SQL;
		$this->db->query($s);
		# move everyone outdoors who was previously indoors
		$s = <<<SQL
			update actor a
			join map_cell mc on mc.map = a.map and mc.x = a.x and mc.y = a.y
			set a.indoors = 0, a.evtm = 1, a.evts = 1
			where indoors = 1 and building is NULL
SQL;
		$this->db->query($s);
		# recalculate town boundaries
		$s = <<<SQL
			select t.map, town, name, count(c.building) as bldg,
				floor(3.14 * pow(radius, 2)) as area
			from town t
			join map_cell c on t.map = c.map
				and sqrt(pow(c.x - t.x, 2) + pow(c.y - t.y, 2)) <= t.radius
			left join building_progress bp on c.map = bp.map
				and c.building = bp.building
			where bp.building is null
			group by t.map, town
SQL;
		$q = $this->db->query($s);
		$r = $q->result_array();
		
		foreach($r as $row) {
			# grow if building coverage is >= 60% rounded down
			if($row['bldg'] >= floor($row['area'] * 0.5))
			{
				$s = <<<SQL
					update town set radius = radius + 1
					where map = ? and town = ? and radius < 10
SQL;
				$this->db->query($s, array($row['map'], $row['town']));
				if($this->db->affected_rows() > 0)
					echo "The town of {$row['name']} grew.\n";
			}
			# shrink if building coverage is <= 20% rounded down
			else if($row['bldg'] <= floor($row['area'] * 0.2)
				&& $row['area'] > 12)
			{
				$s = <<<SQL
					update town set radius = radius - 1
					where map = ? and town = ?
SQL;
				$this->db->query($s, array($row['map'], $row['town']));
				echo "The town of {$row['name']} shrank.\n";
			}
		}
		
		# prune events
		$since = time() - 1209600;
		$sql = <<<SQL
		        delete from event_thread where event not in
		        (select event from event where stamp > ?)
SQL;
		$this->db->query($sql, array($since));
		$sql = <<<SQL
		        delete from event where event not in
		        (select event from event_thread)
SQL;
		$this->db->query($sql);
	}
}
