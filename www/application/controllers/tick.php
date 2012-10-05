<?php if(! defined('BASEPATH')) exit();

class tick extends CI_Controller
{
	function tick()
	{
		# don't allow web access to this controller
		if(! defined('CMD'))
			die(show_404());
		parent::__construct();
		$this->load->database();
	}

	# ticks ====================================================================
	function fire($tick = 20)
	{
		$this->load->model('effects');
		$this->load->model('actor');
		$sql = '';

		switch($tick)
		{
			# every 5 minutes --------------------------------------------------
			case 5:
			{
				# handle inspirations
				$sql = <<<SQL
					delete from pdata where dtype = 'effect'
						and dkey like 'insp_%'
						and cast(dval as signed) < 0
SQL;
				$this->db->query($sql);
				$sql = <<<SQL
					update pdata set dval = cast(dval as signed) - 1
					where dtype = 'effect' and dkey like 'insp_%'
SQL;
				$this->db->query($sql);		
				break;
			}
			# every 20 minutes -------------------------------------------------
			case 20:
			{
				# AP regeneration
				$sql = <<<SQL
					update actor set
						stat_hp = (case
							when stat_hpmax = 1000 then stat_hpmax
							else stat_hp
						end),
						stat_ap = (case
							when stat_apmax = 1000 then stat_apmax
							when stat_ap >= stat_apmax then stat_ap
							else least(stat_apmax, stat_ap + 2)
						end),
						stat_mp = (case
							when stat_mpmax = 1000 then stat_mpmax
							when stat_mp >= stat_mpmax then stat_mp
							else least(stat_mpmax, stat_mp + 1)
						end),
						evts = 1
SQL;
				$this->db->query($sql);
				$sql =
					'update actor set stat_ap = stat_apmax where stat_apmax = 1000';
				$this->db->query($sql);
				$sql = 
					'update actor set stat_mp = stat_mpmax where stat_mpmax = 1000';
				$this->db->query($sql);
				
				# perishable items counter
				$sql = <<<SQL
					update actor_item set lifespan = lifespan - 1
					where lifespan is not null
SQL;
				$this->db->query($sql);
				$sql = <<<SQL
					select actor, ai.inum, eq_slot, iname from actor_item ai
					join item i on ai.inum = i.inum
					where ai.lifespan <= 0
SQL;
				$q = $this->db->query($sql);
				$r = $q->result_array();
				$who = array();
				
				foreach($r as $row)
				{
					if($who['actor'] != $row['actor'])
						$who = $this->actor->getInfo($row['actor']);
					if($row['eq_slot'])
						$this->actor->removeItems($row['inum'], $who);
					$this->actor->sendEvent("Your {$row['iname']} perished.",
						$row['actor']);
				}
				
				$sql = 'delete from actor_item where lifespan <= 0';
				$this->db->query($sql);
				
				# clear NPC bodies
				$s = <<<SQL
					update actor
					set map = 0 - map, last = UNIX_TIMESTAMP()
					where user < 0 and map > 0 and stat_hp <= 0
SQL;
				$this->db->query($s);
	
				# NPC spawning
				$q = $this->db->query('select abbrev from npc');
				$r = $q->result_array();
				
				foreach($r as $row)
				{
					$which = "n_{$row['abbrev']}";
					$this->load->model("npcs/{$which}");
					$this->$which->spawn();
				}

				break;
			}
			# every hour -------------------------------------------------------
			case 60:
			{
				# TODO: bid/auction cancellation -> refund timers (wait 24h)
				# auction countdown/expiration			
				$this->load->model('action');
				$this->db->query(
					'update auction_sale set time_left = time_left - 1');
				$q = $this->db->query(
					'select auction, actor from auction_sale where time_left = 0');
				$r = $q->result_array();
				
				foreach($r as $row) {
					$items = $this->action->_getItemString(
						$this->action->_getAuctionItems($row['auction']));
					$this->actor->sendEvent(
						"Your auction has closed, and will expire in 24 hours. ("
							. $items . ")",
						$row['actor']
						);
				}
				
				$q = $this->db->query(
					'select auction, actor from auction_sale where time_left <= -24'
					);
				$r = $q->result_array();
				
				foreach($r as $row) {
					$q = $this->db->query(
						'select actor from auction_bid where auction = ?',
						array($row['auction'])
						);
					$actors = array();
					$rr = $q->result_array();
					foreach($rr as $rrow) $actors[] = $rrow['actor'];
					$items = $this->action->_getItemString(
						$this->action->_getAuctionItems($row['auction']));
					$this->actor->sendEvent(
						"An auction you were bidding on has expired. ({$items})",
						$actors
						);
					$this->actor->sendEvent("Your auction has expired. ({$items})",
						$row['actor']);
					$this->action->_closeAuction($row['auction']);
					echo "Closing auction {$row['auction']}\n";
				}

				break;
			}
			# every day --------------------------------------------------------
			case 0:
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
						$this->actor->sendEvent(
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
				$this->load->model('map');
				
				foreach($r as $row) {
					if($row['owner'])
						$this->actor->sendEvent(
							"Your building ({$row['descr']} [{$row['x']},{$row['y']}]) has collapsed.",
							$row['owner']
							);
					$this->map->sendCellEvent(
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
				break;
			}
		}

		$sql = <<<SQL
			select abbrev, tick from effect_trigger et
			join effect e on et.effect = e.effect
			where tick = ?
SQL;
		$query = $this->db->query($sql, array($tick));
		
		if($query->num_rows() > 0) {
			$res = $query->result_array();
			
			foreach($res as $r)
			{
				$which = "e_{$r['abbrev']}";
				$this->load->model("effects/{$which}");
				print_r(call_user_func(array($this->$which, "tick")));
			}
		}
		
		echo "Tick fired.\n";
	}
}
