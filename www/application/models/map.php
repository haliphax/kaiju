<?php if(! defined('BASEPATH')) exit();

class map extends CI_Model
{
	public $minisize = 6;
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}

	# get map info =============================================================
	function getInfo($map)
	{
		$sql = <<<SQL
			select m.map, mname, max(x) as h, max(y) as w from map m
			join map_cell mc on m.map = mc.map
			where mc.map = ?
			group by mc.map
SQL;
		$q = $this->db->query($sql, array($map));
		return $q->row_array();
	}

	# get minimap image ========================================================
	function getGif($map, $x = 1, $y = 1, $radius = false, $blocksize = false)
	{
		if($blocksize === false) $blocksize = $this->minisize;
		$h = $w = 0;
		
		if($radius === false)
		{
			$sql =
				'select max(x) as h, max(y) as w from map_cell where map = ?';
			$q = $this->db->query($sql, array($map));
			$r = $q->row_array();
			$h = $r['h'];
			$w = $r['w'];
		}
		else
			$h = $w = $radius * 2 + 1;

		$sql = <<<SQL
			select distinct mc.tile, hexcolor from map_cell mc
			join tile t on mc.tile = t.tile
			where map = ?
SQL;
		$q = $this->db->query($sql, array($map));
		$r = $q->result_array();
		$tiles = array();
		foreach($r as $t)
			$tiles[$t['tile']] = $t['hexcolor'];
		$sql = 'select tile, x, y from map_cell where map = ? ';
		$params = array($map);
		
		if($radius !== false)
		{
			$sql .= 'and x >= ? and x <= ? and y >= ? and y <= ?';
			$params[] = $x - $radius;
			$params[] = $x + $radius;
			$params[] = $y - $radius;
			$params[] = $y + $radius;
		}
		
		$sql .= ' order by x, y';
		$q = $this->db->query($sql, $params);
		$r = $q->result_array();
		$map = array();
		$offset = 1;
		$wrap = $w;
		$origx = $x;
		$origy = $y;
		
		if($radius !== false)
		{
			$x = $x - $radius;
			$y = $y - $radius;
			$offset = $origy - $radius;
			$wrap = $origy + $radius;
		}
		
		foreach($r as $c)
		{
			while($c['x'] != $x || $c['y'] != $y)
			{
				$map[] = 0;
				
				if(++$y > $wrap)
				{
					$x++;
					$y = $offset;
				}
			}
			
			if(++$y > $wrap)
			{
				$x++;
				$y = $offset;
			}
			
			$map[] = $c['tile'];
		}
	
		$im = new Imagick();
		$im->newImage($w, $h, new ImagickPixel("white"));
		$it = $im->getPixelIterator();
		$count = 0;
		
		foreach($it as $row => $pixels)
		{
			foreach($pixels as $col => $px)
			{
				$px->setColor('#' . $tiles[$map[$count++]]);
			}
			
			$it->syncIterator();
		}
		
		$im->setImageFormat('gif');
		$im->resizeImage($w * $blocksize, $h * $blocksize, imagick::FILTER_BOX,
			1);
		return $im;
	}
	
	# send map cell an event, with optional exclusions =========================
	function sendCellEvent($event, $excludes, $map, $x, $y, $i)
	{
		$this->db->trans_start();
		{
			# create event
			$sql = 'insert into event (descr, stamp) values (?, ?)';
			$query = $this->db->query($sql, array($event, time()));
			
			if($this->db->affected_rows() <= 0)
			{
				$this->db->trans_complete();
				return false;				
			}
			
			$e = $this->db->insert_id();
			$sql = <<<SQL
				select actor from actor
				where map = ? and x = ? and y = ? and indoors = ?
					and stat_hp > 0
					and actor not in (select actor from actor_npc)
SQL;
			$query = $this->db->query($sql, array($map, $x, $y, $i));
			# nobody else to tell!
			if($query->num_rows() == 0) return true;
			$res = $query->result_array();
			
			# create threads
			foreach($res as $r)
			{
				# check for exclusion
				if(in_array($r['actor'], $excludes)) continue;
				$sql = 'insert into event_thread (event, actor) values (?, ?)';
				$query = $this->db->query($sql, array($e, $r['actor']));
				if($this->db->affected_rows() <= 0) return false;
			}
		}
		$this->db->trans_complete();
		
		return true;
	}
	
	# set evts for given cell ==================================================
	function setCellEvtS($map, $x, $y, $i)
	{
		$sql = <<<SQL
			update actor set evts = 1
			where map = ? and x = ? and y = ? and indoors = ?
				and actor not in (select actor from actor_npc)
SQL;
		$this->db->query($sql, array($map, $x, $y, $i));
	}
	
	# get map given map, x, y, and radius ======================================
	function getMap($map, $x, $y, $i, $d, $radius)
	{
		$extra = '';
		$tile = '';
		$dense = 0;
		
		# only grab same building's cells if indoors
		if($i)
		{
			$sql = <<<SQL
				select building from map_cell where map = ? and x = ? and y = ?
SQL;
			$query = $this->db->query($sql, array($map, $x, $y));
			$res = $query->row_array();
			$extra = "and c.building = {$res['building']}";
			$tile = "'indoors.gif' as";
		}
		
		# if in a dense cell, only show surrounding cells, otherwise hide dense
		if($d == 1)
		{
			$radius = 1;
			$dense = 1;
		}
		
		$sql = <<<SQL
			select c.x, c.y, ifnull(c.descr, t.descr) as descr, {$tile} img,
				(case
					when c.building is null
					then ''
					else concat(
						(case
							when c.building in
								(select building from map_cell
								where map_cell.x = c.x - 1 and map_cell.y = c.y
									and map_cell.map = c.map)
							then ''
							else 'n'
							end),
							concat(
							(case
								when c.building in
									(select building from map_cell
									where map_cell.x = c.x + 1
										and map_cell.y = c.y
										and map_cell.map = c.map)
								then ''
								else 's'
								end),
								concat(
									(case when c.building in
											(select building from map_cell
											where map_cell.x = c.x
												and map_cell.y = c.y - 1
												and map_cell.map = c.map)
										then ''
										else 'w'
										end),
									(case when c.building in 
											(select building from map_cell 
											where map_cell.x = c.x
												and map_cell.y = c.y + 1 
												and map_cell.map = c.map)
										then ''
										else 'e'
										end)
								)
							)
						)
					end) as w,
				(case when t.dense = b'{$dense}'
					then count(distinct a.actor)
					else 0 end) as occ,
				cs.clan
			from map_cell c
			left join tile t on c.tile = t.tile
			left join actor a on a.map = c.map and a.x = c.x and a.y = c.y
				and a.stat_hp > 0 and a.indoors = ?
				and (a.actor = ? or a.actor not in
					(select aa.actor from actor aa
					join actor_effect aae on aa.actor = aae.actor
					join effect_hide eeh on aae.effect = eeh.effect
					where aa.actor = a.actor))
			left join clan_stronghold cs on c.map = cs.map
				and cs.building = c.building
			where c.x >= ? and c.x <= ? and c.y >= ? and c.y <= ? and c.map = ?
			{$extra}
			group by c.map, c.x, c.y
			order by c.x, c.y asc
SQL;
		$query = $this->db->query($sql, array($i,
			$this->session->userdata('actor'),
			$x - $radius, $x + $radius, $y - $radius, $y + $radius, $map));
		return $query->result_array();
	}

	# get cell occupants =======================================================
	function getCellOccupants($map, $x, $y, $i, $hidden = false)
	{
		$hidingsql = '';
		if($hidden === false)
			$hidingsql = <<<SQL
				and a.actor not in
					(select a.actor from actor a
					join actor_effect ae on a.actor = ae.actor
					join effect_hide eh on ae.effect = eh.effect)
SQL;
		$sql = <<<SQL
			select aname, a.actor, a.faction, clan,
				(case when sum(
					(case when ee.effect is null
					then 0
					else 1 end)) > 0
				then 1
				else 0 end)
					as elev
				from actor a
			left join actor_effect ae on a.actor = ae.actor
			left join clan_actor ca on a.actor = ca.actor
			left join effect_elevated ee on ae.effect = ee.effect
			where map = ? and x = ? and y = ? and indoors = ?
				and a.stat_hp > 0
				{$hidingsql}
			group by a.actor
			order by elev desc, aname asc
SQL;
		$query = $this->db->query($sql, array($map, $x, $y, $i));
		return $query->result_array();
	}
	
	# get cell corpses =========================================================
	function getCellCorpses($map, $x, $y, $i)
	{
		$sql = <<<SQL
			select count(actor) as cnt from actor
				where map = ? and x = ? and y = ? and stat_hp <= 0
				and indoors = ?
SQL;
		$query = $this->db->query($sql, array($map, $x, $y, $i));
		$ret = $query->row_array();
		return $ret['cnt'];
	}

	# search a cell ============================================================
	function searchCell(&$actor, $map = false, $x = false, $y = false,
		$i = false)
	{
		if($map === false) $map = $actor['map'];
		if($x === false) $x = $actor['x'];
		if($y === false) $y = $actor['y'];
		if($i === false) $i = $actor['indoors'];
		$sql = <<<SQL
			select sum(odds) as tot from map_cell_search
			where map = ? and x = ? and y = ? and indoors = ?
SQL;
		$query = $this->db->query($sql, array($map, $x, $y, $i));
		$res = $query->row_array();
		$tot = $res['tot'];
		
		# cell search
		if($tot > 0)
		{
			$sql = <<<SQL
				select inum, odds from map_cell_search
				where map = ? and x = ? and y = ? and indoors = ?
				order by rand()
SQL;
			$query = $this->db->query($sql, array($map, $x, $y, $i));
		}
		else
		{
			$num = 0;
			
			if($i == 1)
			{
				# building search
				$sql = <<<SQL
					select building, sum(odds) as tot from building_search
					where map = ? and building in
						(select building from map_cell
						where map = ? and x = ? and y = ?)
					group by building
SQL;
				$query = $this->db->query($sql, array($map, $map, $x, $y));
				$res = $query->row_array();
				$tot = $res['tot'];
			}
			
			if($tot > 0)
			{
				$b = $res['building'];
				$sql = <<<SQL
					select inum, odds from building_search
					where building = ? and map = ?
					order by rand()
SQL;
				$query = $this->db->query($sql, array($b, $map));
			}
			else
			{
				$num = 0;
				
				if($i == 1)
				{
					# structure search
					$sql = <<<SQL
						select ss.structure as structure, sum(odds) as tot
						from map_cell mc
						left join building b on mc.building = b.building
						left join structure_search ss
							on b.structure = ss.structure
						where mc.map = ? and mc.x = ? and mc.y = ?
						group by structure
SQL;
					$query = $this->db->query($sql, array($map, $x, $y));
					$res = $query->row_array();
					$tot = $res['tot'];
				}
				
				if($tot > 0)
				{
					$sql = <<<SQL
						select inum, odds from structure_search
						where structure = ?
						order by rand()
SQL;
					$query = $this->db->query($sql, array($res['structure']));
				}
				else
				{
					# tile search
					$sql = <<<SQL
						select ts.tile as tile, sum(odds) as tot
						from tile_search ts
						left join map_cell mc on mc.tile = ts.tile
						where map = ? and x = ? and y = ?
						and (indoors = ? or indoors is null)
						group by tile
SQL;
					$query = $this->db->query($sql, array($map, $x, $y, $i));
					
					if($query->num_rows > 0)
					{
						$res = $query->row_array();
						$tot = $res['tot'];
						
						if($tot > 0)
						{
							$sql = <<<SQL
								select inum, odds from tile_search
								where tile = ?
								order by rand()
SQL;
							$query = $this->db->query($sql, array(
								$res['tile']));
						}
					}
				}
			}
		}
		
		$roll = 0;
		$foundactor = false;
		$revealodds = 7;

		if($tot <= 0)
			$roll = rand(1, 20);
		else
		{
			$revealodds = $tot / 5;
			$roll = rand(1, $tot);
		}
		
		# did we find a hidden character?
		if($roll <= $revealodds)
		{
			$sql = <<<SQL
				select a.actor as actor, aname from actor a
				join actor_effect ae on a.actor = ae.actor
				join effect_hide eh on ae.effect = eh.effect
				where map = ? and x = ? and y = ? and indoors = ?
					and a.actor != ?
				order by rand()
				limit 1
SQL;
			$qquery = $this->db->query($sql, array($map, $x, $y, $i,
				$actor['actor']));
				
			if($qquery->num_rows() > 0)
			{
				$rres = $qquery->row_array();
				$foundactor = $rres['aname'];
				$this->setRadiusEvtM($actor['map'], $actor['x'], $actor['y'],
					$b);
				$this->ci->load->model('actor');
				$this->ci->actor->removeEffect('hiding',
					$this->ci->actor->getInfo($rres['actor']));
				$this->ci->actor->sendEvent($actor['aname']
					. ' discovered your location!', $rres['actor']);
			}
		}
		
		if($tot <= 0) return array(false, $foundactor);
		if($query->num_rows() <= 0) return array(false, $foundactor);
		$res = $query->result_array();
		$counter = 0;
		
		foreach($res as $r)
		{
			$counter += $r['odds'];
			
			# found an item
			if($roll <= $counter)
			{
				if($r['inum'] == 0) return array(false, $foundactor);
				$this->ci->load->model('item');
				$item = $this->ci->item->getBaseInfo($r['inum']);
				$sql = <<<SQL
					insert into actor_item
						(inum, actor, durability, durmax, lifespan)
					values (?, ?, ?, ?, ?)
SQL;
				$query = $this->db->query($sql, array($r['inum'],
					$actor['actor'], $item['durability'],
					($item['repair'] ? $item['durability'] : 0),
					$item['lifespan']));
				if($this->db->affected_rows() <= 0)
					return array('[ERROR]', $foundactor);
				$sql = 'select iname from item where inum = ?';
				$query = $this->db->query($sql, array($r['inum']));
				$res = $query->row_array();
				return array($res['iname'], $foundactor);
			}
		}
	}
	
	function getBuildingLocation($map, $building)
	{
		$q = $this->db->query(
			'select map, x, y from map_cell where map = ? and building = ?',
			$map, $building);
		return $q->row_array();
	}

	# get cell information =====================================================
	function getCellInfo($map, $x, $y)
	{
		$this->ci->load->model('common');
		
		if($this->ci->common->cellinfo)
			if(array_key_exists('map', $this->ci->common->cellinfo)
				&& $this->ci->common->cellinfo['map'] == $map
				&& $this->ci->common->cellinfo['x'] == $x
				&& $this->ci->common->cellinfo['y'] == $y)
			{
				return $this->ci->model->cellinfo;
			}
		
		$sql = <<<SQL
			select x, y, c.building, c.tile, clan,
				ifnull(ifnull(c.descr, b.descr), t.descr) as descr,				
				(case when t.dense = b'1' then 1 else 0 end) as dense				
			from map_cell c
			left join tile t on c.tile = t.tile
			left join building b on c.building = b.building and c.map = b.map
			left join structure s on b.structure = s.structure
			left join clan_stronghold cs on cs.map = c.map
				and cs.building = c.building
			where c.map = ? and c.x = ? and c.y = ?
SQL;
		$query = $this->db->query($sql, array($map, $x, $y));
		$this->ci->common->cellinfo = $query->row_array();
		$sql = <<<SQL
			select town, name, radius,
				round(sqrt(pow(? - x, 2) + pow(? - y, 2))) as d
			from town
			where map = ?
				and ? >= (x - radius) and ? <= (x + radius)
				and ? >= (y - radius) and ? <= (y + radius)
			order by d asc
			limit 1
SQL;
		$query = $this->db->query($sql, array($x, $y, $map, $x, $x, $y, $y));
		$r = $query->row_array();
		if(isset($r['town']) && $r['d'] <= $r['radius'])
			$this->ci->common->cellinfo['town'] = $r['name'];
		return $this->ci->common->cellinfo;
	}

	# get cell model "arrive" triggers =========================================
	function getCellArriveTriggers($map, $x, $y, $i)
	{
		$s = <<<SQL
			select abbrev from map_cell_trigger
				where arrive = b'1' and map = ? and x = ? and y = ?
					and (indoors = ? or indoors = -1)
			union
			select abbrev from map_cell_class mcc
				join class_cell_trigger cct on mcc.cclass = cct.cclass
				join class_cell cc on mcc.cclass = cc.cclass
				where arrive = b'1' and map = ? and x = ? and y = ?
					and (indoors = ? or indoors = -1)
			union
			select abbrev from map_cell c
				join tile_trigger tt on c.tile = tt.tile
				join tile t on tt.tile = t.tile
				where arrive = b'1' and c.map = ? and c.x = ? and c.y = ?
SQL;
		$q = $this->db->query($s, array($map, $x, $y, $i, $map, $x, $y, $i,
			$map, $x, $y));
		$triggers = array();
		$r = $q->result_array();
		foreach($r as $row) $triggers[] = $row['abbrev'];
		return $triggers;
	}
	
	function getCellLeaveTriggers($map, $x, $y, $i)
	{
		$s = <<<SQL
			select abbrev from map_cell_trigger
				where `leave` = b'1' and map = ? and x = ? and y = ?
					and (indoors = ? or indoors = -1)
			union
			select abbrev from map_cell_class mcc
				join class_cell_trigger cct on mcc.cclass = cct.cclass
				join class_cell cc on mcc.cclass = cc.cclass
				where `leave` = b'1' and map = ? and x = ? and y = ?
					and (indoors = ? or indoors = -1)
			union
			select abbrev from map_cell c
				join tile_trigger tt on c.tile = tt.tile
				join tile t on tt.tile = t.tile
				where `leave` = b'1' and c.map = ? and c.x = ? and c.y = ?
SQL;
		$q = $this->db->query($s, array($map, $x, $y, $i, $map, $x, $y, $i,
			$map, $x, $y));
		$triggers = array();
		$r = $q->result_array();
		foreach($r as $row) $triggers[] = $row['abbrev'];
		return $triggers;
	}
	
	# update map evt for given radius ==========================================
	function setRadiusEvtM($map, $x, $y, $b = false, $r = 3)
	{
		if($b === false)
		{
			$ret = $this->getCellInfo($map, $x, $y);
			$b = $ret['building'];
		}
		
		$query = 0;
		$bottom = $x + $r;
		$top = $x - $r;
		$right = $y + $r;
		$left = $y - $r;
		
		if($b)
		{
			$sql = <<<SQL
				update actor set evtm = 1
				where x <= ? and x >= ? and y <= ? and y >= ? and map = ?
					and ? in
						(select building from map_cell
						where x <= ? and x >= ? and y <= ? and y >= ?
							and map = ?)
SQL;
			$this->db->query($sql, array($bottom, $top, $right, $left,
				$map, $b, $bottom, $top, $right, $left, $map));
		}
		else
		{
			$sql = <<<SQL
				update actor set evtm = 1
				where x <= ? and x >= ? and y <= ? and y >= ? and map = ?
					and indoors = 0
SQL;
			$this->db->query($sql, array($bottom, $top, $right, $left,
				$map));
		}
	}
	
	# get all building classes =================================================
	function getAllClasses()
	{
		$q = $this->db->query('select sclass as bclass, abbrev from class_structure');
		return $q->result_array();
	}

	# get building classes =====================================================
	function getBuildingClasses($map, $building)
	{
		$s = <<<SQL
			select bclass, abbrev
				from building_class bc
				join class_structure cs on bc.bclass = cs.sclass
				where bc.map = ? and bc.building = ?
SQL;
		$q = $this->db->query($s, array($map, $building));
		return $q->result_array();
	}

	# add class to building ====================================================
	function addBuildingClass($map, $building, $class)
	{
		if(! is_numeric($class))
		{
			$s = 'select sclass from class_structure where abbrev = ?';
            $q = $this->db->query($s, $class);
            if($q->num_rows() <= 0) return false;
            $r = $q->row_array();
            $class = $r['sclass'];
		}

		$s = <<<SQL
			insert ignore into building_class (map, building, bclass)
                values (?, ?, ?)
SQL;
        $this->db->query($s, array($map, $building, $class));
        if($this->db->affected_rows() <= 0) return false;
        return true;
	}

    # remove a class from a building ===========================================
    function removeBuildingClass($map, $building, $class)
    {
		if(! is_numeric($class))
		{
			$s = 'select sclass from class_structure where abbrev = ?';
            $q = $this->db->query($s, $class);
            if($q->num_rows() <= 0) return false;
            $r = $q->row_array();
            $class = $r['sclass'];
		}

        $s = <<<SQL
            delete from building_class
                where building = ? and map = ? and bclass = ?
SQL;
        $this->db->query($s, array($building, $map, $class));
        if($this->db->affected_rows() <= 0) return false;
        return true;
    }

    # building has a particular class? =========================================
    function buildingHasClass($map, $building, $class)
    {
		if(! is_numeric($class))
		{
			$s = 'select sclass from class_structure where abbrev = ?';
            $q = $this->db->query($s, $class);
            if($q->num_rows() <= 0) return false;
            $r = $q->row_array();
            $class = $r['sclass'];
		}

        $s = <<<SQL
            select 1 from building_class
            where map = ? and building = ? and bclass = ?
SQL;
        $q = $this->db->query($s, array($map, $building, $class));
        if($q->num_rows() <= 0) return false;
        return true;
    }

	# get structure associated with building ===================================
	function getBuildingStructure($map, $building)
	{
		$sql = 'select structure from building where map = ? and building = ?';
		$query = $this->db->query($sql, array($map, $building));
		if($query->num_rows() <= 0)
			return false;
		$res = $query->row_array();
		return $res['structure'];
	}
	
	# get surroundings for map cell ============================================
	function getSurroundings($actor, $map, $x, $y, $i)
	{
		$cellinfo = $this->getCellInfo($map, $x, $y);
		$structure = $this->getBuildingStructure($map, $cellinfo['building']);
		$ret = array();
		$sql = <<<SQL
			select surr, cc.abbrev, 'cell' as type,
				(case when gen = b'1' then 1 else 0 end) as gen
				from map_cell_class mcc
				join class_cell cc on mcc.cclass = cc.cclass
				where map = ? and x = ? and y = ? and
				(indoors = ? or indoors = -1)
			union
			select surr, cs.abbrev, 'structure' as type,
				(case when gen = b'1' then 1 else 0 end) as gen
				from building_class bc
				join class_structure cs on bc.bclass = cs.sclass
				where map = ? and building = ? and
				(indoors = ? or indoors = -1)
			union
			select surr, cs.abbrev, 'structure' as type,
				(case when gen = b'1' then 1 else 0 end) as gen
				from structure_class sc
				join class_structure cs on sc.sclass = cs.sclass
				where structure = ? and
				(indoors = ? or indoors = -1)
			union
			select surr, cc.abbrev, 'cell' as type,
				(case when gen = b'1' then 1 else 0 end) as gen
				from tile_class tc
				join class_cell cc on tc.tclass = cc.cclass
				where tile = ? and
				(indoors = ? or indoors = -1)
SQL;
		$q = $this->db->query($sql, array($map, $x, $y, $i,
			$map, $cellinfo['building'], $i, $structure, $i, $cellinfo['tile'],
			$i));
		$r = $q->result_array();
		
		foreach($r as $row)
		{
			if($row['surr']) $ret[] = $row['surr'];
			if($row['gen'] == 0) continue;
			$which = "mc_{$row['abbrev']}";
			$type = $row['type'];			
			$this->ci->load->model("classes/{$type}/{$which}");
			$ret[] = call_user_func(
				array($this->ci->$which, 'surr'), $actor);
		}
		
		if($i == 1)
		{
			$q = $this->db->query(
				'select surr_i from building where map = ? and building = ?',
				array($map, $cellinfo['building']));
			$r = $q->result_array();
			foreach($r as $row)
				if($row['surr_i'])
				{
					$txt = html_entity_decode($row['surr_i']);
					$ret[] = "<span class='surr'>{$txt}</span>";
				}
		}
		else
		{
			$q = $this->db->query(
				'select surr from building where map = ? and building = ?',
				array($map, $cellinfo['building']));
			$r = $q->result_array();
			foreach($r as $row)
				if($row['surr'])
				{
					$txt = html_entity_decode($row['surr']);
					$ret[] = "<span class='surr'>{$txt}</span>";
				}
		}
		
		return $ret;
	}

	# get a cell's classes =====================================================
	function getCellClasses($map, $x, $y, $i, &$cellinfo)
	{
		$cellinfo = $this->getCellInfo($map, $x, $y);
		$t = $cellinfo['tile'];
		$b = $cellinfo['building'];
		if(! $b) $b = 0;
		$s = $this->getBuildingStructure($map, $b);
		if(! $s) $s = 0;
		
		$sql = <<<SQL
			select cc.abbrev from map_cell_class mcc
				join class_cell cc on mcc.cclass = cc.cclass
				where map = ? and x = ? and y = ? and
				(indoors = ? or indoors = -1)
			union
			select cc.abbrev from building_class bc
				join class_structure cc on bc.bclass = cc.sclass
				where map = ? and building = ? and
				(indoors = ? or indoors = -1)
			union
			select cc.abbrev from structure_class sc
				join class_structure cc on sc.sclass = cc.sclass
				where structure = ? and
				(indoors = ? or indoors = -1)
			union
			select cc.abbrev from tile_class tc
				join class_cell cc on tc.tclass = cc.cclass
				where tile = ? and
				(indoors = ? or indoors = -1)
SQL;
		$query = $this->db->query($sql, array(
			$map, $x, $y, $i, $map, $b, $i, $s, $i, $t, $i
			));
		$res = $query->result_array();
		$ret = array();
		foreach($res as $r) $ret[] = $r['abbrev'];
		return $ret;
	}
	
	# check if cell has a class ================================================
	function cellHasClass($class, $map, $x, $y, $i, &$cellinfo)
	{
		$cellinfo = $this->getCellInfo($map, $x, $y);
		$t = $cellinfo['tile'];
		$b = $cellinfo['building'];
		if(! $b) $b = 0;
		$s = $this->getBuildingStructure($map, $b);
		if(! $s) $s = 0;
		
		$sql = <<<SQL
			select 1 from map_cell_class mcc
				join class_cell cc on mcc.cclass = cc.cclass
				where map = ? and x = ? and y = ? and
				(indoors = ? or indoors = -1)
				and lower(cc.abbrev) = lower(?)
				limit 1
			union 
			select 1 from building_class bc
				join class_structure cc on bc.bclass = cc.sclass
				where map = ? and building = ? and
				(indoors = ? or indoors = -1)
				and lower(cc.abbrev) = lower(?)
				limit 1
			union
			select 1 from structure_class sc
				join class_structure cc on sc.sclass = cc.sclass
				where structure = ? and
				(indoors = ? or indoors = -1)
				and lower(cc.abbrev) = lower(?)
				limit 1
			union
			select 1 from tile_class tc
				join class_cell cc on tc.tclass = cc.cclass
				where tile = ? and
				(indoors = ? or indoors = -1)
				and lower(cc.abbrev) = lower(?)
				limit 1
SQL;
		$query = $this->db->query($sql, array($map, $x, $y, $i, $class, $map,
			$b, $i, $class, $s, $i, $class, $t, $i, $class));
		if($query->num_rows() > 0) return true;
		return false;
	}
	
	# get building owner =======================================================
	function buildingInfo($map, $bldg)
	{
		$s = <<<SQL
			select owner, aname as owner_name, descr, hp, surr, surr_i
			from building b
			left join actor a on b.owner = a.actor
			where b.map = ? and b.building = ?
SQL;
		$q = $this->db->query($s, array($map, $bldg));
		return $q->row_array();
	}

	# get list of needed items =================================================
	function buildingNeeds($map, $bldg)
	{
		$s = <<<SQL
			select iname, amt from building_progress bp
			join item i on bp.inum = i.inum
			where building = ? and map = ?
SQL;
		$q = $this->db->query($s, array($bldg, $map));
		$needed = "";
		$r = $q->result_array();
		
		foreach($r as $row)
		{
			if($needed) $needed .= ", ";
			$needed .= $row['amt'] . "x " . $row['iname'];
		}
		
		return $needed;
	}

	# check to see if tile is under construction ===============================
	function tileIsUnderConstruction($map, $bldg)
	{
		# no building found on cell
		if(! $bldg) return false;
		$s = 'select 1 from building_progress where map = ? and building = ?';
		$q = $this->db->query($s, array($map, $bldg));
		if($q->num_rows() <= 0) return false;
		return true;
	}
	
	# what kind of structure is it? ============================================
	function siteStructureName($map, $bldg)
	{
		$s = <<<SQL
			select s.descr from map_cell c
			join building b on c.map = b.map and c.building = b.building
			join structure s on b.structure = s.structure
			where c.map = ? and c.building = ?
SQL;
		$q = $this->db->query($s, array($map, $bldg));
		$r = $q->row_array();
		return $r['descr'];
	}
}
