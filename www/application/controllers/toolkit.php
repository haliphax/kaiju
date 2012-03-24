<?php if(! defined('BASEPATH')) exit();

# WARNING: THIS CONTROLLER SHOULD BE USED FOR DEVELOPMENT PUPROSES ONLY!
# BE SURE TO DISABLE IT WHEN MIGRATING CODE TO A PRODUCTION ENVIRONMENT!

class toolkit extends CI_Controller
{
	function toolkit()
	{
		#die(show_404());
		parent::__construct();
		$this->load->database();
	}
	
	# search odds for forum ====================================================
	function searchodds()
	{
		$sql = 'select * from x_v_search_odds';
		$query = $this->db->query($sql);
		$res = $query->result_array();
		echo '[table][tr][th]Tile[/th][th]Item[/th][th]Odds[/th][/tr]';
		foreach($res as $r)
			echo "[tr][td]{$r['tile']}[/td][td]{$r['item']}[/td][td]{$r['odds']}%[/td][/tr]";
		echo '[/table]';
	}
	
	# copy user to dev =========================================================
	function user2dev($user)
	{
		$sql = 'delete from user where user = ?';
		$this->db->query($sql, array($user));
		$sql = <<<SQL
			insert into user
			select * from kaiju_rpg.user where user = ?
SQL;
		$this->db->query($sql, array($user));
		if($this->db->affected_rows() <= 0)
			echo "Copy failed.";
		else
			echo "User copied.";
		echo PHP_EOL;
	}
	
	# copy char to dev =========================================================
	function actor2dev($actor)
	{
		$sql[] = 'delete from actor where actor = ?';
		$sql[] = <<<SQL
			delete from item_weapon_ammo where instance in
				(select instance from actor_item where actor_item.actor = ?)
SQL;
		$sql[] = 'delete from actor_item where actor = ?';
		$sql[] = 'delete from pdata where owner = ?';
		$sql[] = 'delete from actor_effect where actor = ?';
		$sql[] = <<<SQL
			insert into actor_item
				(select * from kaiju_rpg.actor_item where actor = ?)
SQL;
		$sql[] = <<<SQL
			insert into pdata
				(select * from kaiju_rpg.pdata where owner = ?)
SQL;
		$sql[] = <<<SQL
			insert into actor_effect
				(select * from kaiju_rpg.actor_effect where actor = ?)
SQL;
		$sql[] = <<<SQL
			insert into actor
				(select * from kaiju_rpg.actor where actor = ?)
SQL;
		foreach($sql as $s)
			$this->db->query($s, array($actor));
		if($this->db->affected_rows() <= 0) die("Copy failed.\n");
		echo "User copied.\n";
	}
	
	# show whole map ===========================================================
	function bigmap($map)
	{
		$sql = <<<SQL
			select max(x) as max_x , max(y) as max_y from map_cell where map = ?
SQL;
		$q = $this->db->query($sql, $map);
		$r = $q->row_array();
		$w = $r['max_y'] * 60;
		$h = $r['max_x'] * 60;
		echo <<<HTML
			<!DOCTYPE HTML>
			<html>
				<head>
					<title>Big Map: {$map}</title>
					<style type="text/css">
						.c { width: 60px; height: 60px; margin: 0; padding: 0; float: left; }
						.c img { position: relative; margin: 0; }
						.c img.w { top: -60; }
						.m { height: {$h}px; width: {$w}px; }
					</style>
				</head>
				<body>
					<div class="m">
HTML;
		$sql = <<<SQL
			select c.x, c.y, ifnull(c.descr, t.descr) as descr, img,
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
			left join tile t on c.tile = t.tile
			where c.map = ?
			group by c.map, c.x, c.y
			order by c.x, c.y asc
SQL;
		$q = $this->db->query($sql, $map);
		$r = $q->result_array();
		foreach($r as $row)
			echo "<div class=\"c\" style=\"background:url('/images/tiles/{$row['img']}');\">"
				. "<img src='/images/tiles/box.gif' />"
				. ($row['w']
					? "<img class='w' src='/images/walls/{$row['w']}.png' />"
					: "")
				. "</div>";
		echo "</div></body></html>";
	}
	
	function biggif($map)
	{
		$this->load->model('map');
		$this->output->set_header("Content-Type: image/gif");
		echo $this->map->getGif($map, 1, 1, false, 5);
	}

	# create new map ===========================================================
	function buildmap()
	{
		return;
		
		$map = 5;
		for($a = 1; $a <= 50; $a++)
			for($b = 1; $b <= 50; $b++)
				$this->db->query("insert into map_cell (map, x, y, tile) values (?, ?, ?, 3)", array($map, $a, $b));
	}
}
