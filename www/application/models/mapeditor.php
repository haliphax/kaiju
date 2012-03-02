<?php if(! defined('BASEPATH')) exit();

# map editor model =============================================================

class mapeditor extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	# get list of maps =========================================================
	function getMapList()
	{
		$sql = <<<SQL
			select m.map, mname from map m
			join user_priv_mapedit um on (m.map = um.map or 0 = um.map)
				and um.user = ?
			order by lower(mname)
SQL;
		$q = $this->db->query($sql, array($this->session->userdata('user')));
		return $q->result_array();		
	}
	
	# get chunk of map to display/edit =========================================
	function getChunk($map, $top, $left, $size)
	{
		$sql = <<<SQL
			select x, y, ifnull(c.building, 0) as b, c.tile as t,
				ifnull(c.descr, ifnull(b.descr, '')) as d,
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
					end) as w				
			from map_cell c
			join tile ti on c.tile = ti.tile
			left join building b on c.building = b.building
				and c.map = b.map
			where c.map = ? and x >= ? and x < ? and y >= ? and y < ?
			order by x asc, y asc
SQL;
		$q = $this->db->query($sql, array($map, $top, $top + $size, $left,
			$left + $size));
		$x = $top;
		$y = $left;
		$r = $q->result_array();
		$cells = array();
		$tot = $size * $size;
		
		foreach($r as $cell)
		{
			while($x != $cell['x'] || $y != $cell['y'])
			{
				$cells[] = array(
					'x' => $x,
					'y' => $y,
					'b' => 0,
					't' => 0,
					'd' => '',
					'w' => ''
					);
				
				if(++$y	>= $size + $left)
				{
					$y = $left;
					$x++;
				}
			}
			
			$cells[] = $cell;
		
			if(++$y	>= $size + $left)
			{
				$y = $left;
				$x++;
			}
		}
		
		for($a = 0, $cnt = $size * $size - count($cells); $a < $cnt; $a++)
		{
			$cells[] = array(
				'x' => $x,
				'y' => $y,
				'b' => 0,
				't' => 0,
				'd' => '',
				'w' => ''
				);
				
			if(++$y	>= $size + $left)
			{
				$y = $left;
				$x++;
			}
		}
		
		return $cells;
	}
	
	# get tiles ================================================================
	function getTiles()
	{
		$sql = 'select * from tile';
		$q = $this->db->query($sql);
		return $q->result_array();
	}
}
