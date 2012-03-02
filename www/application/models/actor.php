<?php if(! defined('BASEPATH')) exit();

class actor extends CI_Model
{
	private $ci;
	
	function actor()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
	}
	
	function leaveClan($actor)
	{
		$this->db->query('delete from clan_actor where actor = ?', $actor);
		if($this->db->affected_rows() <= 0) return false;
		return true;
	}
	
	function getByName($name)
	{
		$s = 'select actor from actor where lower(aname) = lower(?)';
		$q = $this->db->query($s, $name);
		$r = $q->row_array();
		return $r['actor'];
	}
	
	function acceptInvitation($actor, $clan)
	{
		$this->db->query(
			'delete from clan_invitation where clan = ? and actor = ?',
			array($clan, $actor));
		if($this->db->affected_rows() <= 0) return false;
		$this->db->query('insert into clan_actor (clan, actor) values (?, ?)',
			array($clan, $actor));
		if($this->db->affected_rows() <= 0) return false;
		return true;
	}
	
	function denyInvitation($actor, $clan)
	{
		$this->db->query(
			'delete from clan_invitation where clan = ? and actor = ?',
			array($clan, $actor));
		if($this->db->affected_rows() <= 0) return false;
		return true;
	}
	
	function getInvitations($actor)
	{
		$s = <<<SQL
			select descr, c.clan, msg from clan_invitation ci
			join clan c on ci.clan = c.clan
			where ci.actor = ?
SQL;
		$q = $this->db->query($s, $actor);
		return $q->result_array();
	}
	
	# get data for given character =============================================
	function getInfo($actor)
	{
		if($actor == 0)
		{
			$ret['aname'] = 'the stronghold';
			$ret['actor'] = 0;
			$ret['stat_hp'] = 1;
			return $ret;
		}
		
		$sql = <<<SQL
			select a.*, c.clan, c.descr as clan_name, f.descr as faction_name,
				(case when evts = b'1' then 1 else 0 end) as evts,
				(case when evtm = b'1' then 1 else 0 end) as evtm
			from actor a
			left join faction f on a.faction = f.faction
			left join clan_actor ca on a.actor = ca.actor
			left join clan c on ca.clan = c.clan
			where a.actor = ?
SQL;
		$query = $this->db->query($sql, array($actor));
		return $query->row_array();
	}
	
	# add a skill ==============================================================
	function addSkill($skill, $actor)
	{
		if(! is_numeric($skill))
		{
			$sql = 'select skill from skill where abbrev = ?';
			$query = $this->db->query($sql, array($skill));
			$res = $query->row_array();
			$skill = $res['skill'];
		}
		
		$sql = 'insert ignore into actor_skill (actor, skill) values (?, ?)';
		$this->db->query($sql, array($actor, $skill));
		if($this->db->affected_rows() <= 0) return false;
		return true;
	}
	
	# get actor's classes ======================================================
	function getClasses($actor)
	{
		$sql = <<<SQL
			select ca.abbrev, ca.descr, ac.aclass from actor a
			join actor_class ac on a.actor = ac.actor
			join class_actor ca on ac.aclass = ca.aclass
			where a.actor = ?
SQL;
		$query = $this->db->query($sql, array($actor));
		return $query->result_array();
	}
	
	# check for class ==========================================================
	function hasClass($class, $actor)
	{
		$sql = 0;
		
		if(is_numeric($class))
			$sql = <<<SQL
				select 1 from actor a
				join actor_class ac on a.actor = ac.actor
				where a.actor = ? and ac.aclass = ?
				limit 1
SQL;
		else
			$sql = <<<SQL
				select 1 from actor a
				join actor_class ac on a.actor = ac.actor
				join class_actor ca on ac.aclass = ca.aclass
				where a.actor =? and ca.abbrev = ?
				limit 1
SQL;
		
		$query = $this->db->query($sql, array($actor, $class));
		if($query->num_rows() <= 0) return false;
		return true;
	}	
	
	# get equipment for given character ========================================
	function getEquipment($actor)
	{
		$sql = <<<SQL
			select iname from actor_item ai
			join item i on ai.inum = i.inum
			where actor = ? and eq_slot is not null
			order by eq_slot asc
SQL;
		$query = $this->db->query($sql, array($actor));
		return $query->result_array();
	}
	
	# get events for character =================================================
	function getEvents($actor)
	{
		$ret = false;
		
		$this->db->trans_start();
		{
			$sql = <<<SQL
				select stamp, e.event, descr from event e
				left join event_thread et on e.event = et.event
				where actor = ?
				order by stamp, event asc
SQL;
			$query = $this->db->query($sql, array($actor));
			$ret = $query->result_array();
			$sql = 'delete from event_thread where actor = ?';
			$this->db->query($sql, array($actor));
		}
		$this->db->trans_complete();
		
		return $ret;
	}
	
	# send event to specific actor =============================================
	function sendEvent($event, $actor)
	{
		if(is_array($actor))
		{
			foreach($actor as $a)
				$this->sendEvent($event, $a);
			return true;
		}
		
		$this->db->trans_start();
		{
			# create event
			$sql = 'insert into event (descr, stamp) values (?, ?)';
			$query = $this->db->query($sql, array($event, time()));
			if($this->db->affected_rows() <= 0)
				return false;
			$e = $this->db->insert_id();
			# create thread
			$sql = 'insert into event_thread (event, actor) values (?, ?)';
			$query = $this->db->query($sql, array($e, $actor));
			if($this->db->affected_rows() <= 0)
				return false;
		}
		$this->db->trans_complete();
		
		return true;
	}
	
	# get encumbrance of a given character ====================================
	function getEncumbrance($actor)
	{
		$sql = <<<SQL
			select sum(case when weight = 0 then 1 else weight end) as tot
			from (
				select 
					(case when stack = 0 then ai.instance
					else -1 * ai.inum end) as sort,
					ai.inum, weight * (case when stack = 0 then 1
					else count(ai.inum) end) as weight
				from actor_item ai
				join item i on ai.inum = i.inum
				where ai.actor = ?
				group by sort
			) as tmp
SQL;
		$query = $this->db->query($sql, array($actor));
		$res = $query->row_array();
		return $res['tot'];
	}

	# get inventory for given character ========================================
	function getItems($actor, $class = false)
	{
		$extra = '';
		if($class !== false)
			$extra = <<<SQL
				and '{$class}' in
					(select abbrev from class_item ci
					join item_class ic on ci.iclass = ic.iclass
					where ic.inum = ai.inum)
SQL;
		$sql = <<<SQL
			select distinct
				(case when stack = 0 then ai.instance
				else -1 * ai.inum end) as sort,
				(case when stack = 0 then 1
				else count(ai.inum) end) as num,
				(case when eq_slot = 'MH' and 'melee' not in
					(select distance from item_weapon iw
					where iw.inum = ai.inum)
				then
					(case when aim.ammo is null then 0
					else
						(select
							ifnull(concat(actor_item.inum, '&', iname, ' [',
								(select count(actor_item.instance)
								from actor_item
								where actor_item.inum = aim.ammo
									and actor = ai.actor), ']'),
								0)
							from actor_item
						join item on actor_item.inum = item.inum
						where actor_item.inum = aim.ammo
						limit 1)
					end)
				else NULL end) as ammo,
				(case when ai.durability = 0
				then 'Broken'
				else eq_type end) as eq_type,
				i.inum, ai.instance, iname, eq_slot, weight, target
			from actor_item ai
			join item i on ai.inum = i.inum
			left join actor_item_ammo aim on ai.instance = aim.instance
				and eq_slot is not null
			where ai.actor = ? {$extra}
			group by sort
			order by ifnull(eq_slot, 'Z') asc, lower(iname) asc

SQL;
		$query = $this->db->query($sql, array($actor));
		return $query->result_array();
	}

	# remove item(s) from inventory for given character ========================
	function dropItems($items, $actor)
	{
		$this->db->trans_start();
		{
			foreach($items as $item)
			{
				$sql = <<<SQL
					delete from actor_item
					where instance = ? and actor = ? and eq_slot is null
SQL;
				$query = $this->db->query($sql, array($item, $actor));
				if($this->db->affected_rows() <= 0) return false;
			}
		}
		$this->db->trans_complete();
		
		return true;
	}
	
	# get a character's skills =================================================
	function getSkills($actor, $passive = -1, $class = false, $all = false)
	{
		$clause = ($all ? "" : "and list = b'1'");
		$sql = <<<SQL
			select s.skill, sname, abbrev, cost_mp, cost_ap, passive,
				(case when params = b'1' then 1 else 0 end) as params,
				(case when js = b'1' then 1 else 0 end) as js,
				(case when repeatable = b'1' then 1 else 0 end) as rpt
				from actor_skill a
			join skill s on a.skill = s.skill
			where actor = ? {$clause}
SQL;
		$query = 0;
		$classes = '';
		
		if($class != false)
		{
			$classes = '';
			
			if(is_array($class))
				foreach($class as $k => $c)
					$classes .= ($k > 0 ? ', ' : '') . "'{$c}'";
			else
				$classes .= "'{$class}'";
			$sql .= <<<SQL
				and s.skill in (
					select skill from skill_class where sclass in ({$classes})
					)
SQL;
			$sql .= $classSql;
		}
		
		if($passive > -1)
		{
			$sql .=
				' and (passive = ? or passive = 3) order by lower(sname) asc';
			$query = $this->db->query($sql, array($actor['actor'], $passive));
		}
		else
		{
			$sql .= <<<SQL
				union
				select 0 - ae.effect as skill, ename as sname, abbrev,
					NULL as params, 0 as cost_ap, 0 as cost_mp, 1 as passive,
					0 as js, 0 as rpt
				from actor_effect ae
				join skill_effect se on ae.effect = se.effect
				join effect e on se.effect = e.effect
				where actor = ?
SQL;
			
			if($class != false)
			{
				$classSql = <<<SQL
					and ae.effect in (
						select effect from effect_class where eclass in (
							select eclass from class_effect where abbrev in (
								{$classes})
							)
						)
SQL;
				$sql .= $classSql;
			}
			
			$sql .= ' order by lower(sname) asc';
			$query = $this->db->query($sql,
				array($actor['actor'], $actor['actor']));
		}
		
		$ret = $query->result_array();
		
		if($passive == 2)
			foreach($ret as $k =>$r)
				if($r['params'])
				{
					$skill = $r['abbrev'];
					$this->ci->load->model('skills/' . $skill);
					$ret[$k]['params'] = call_user_func(
						array($this->ci->$skill, 'params'), $actor);
				}
		return $ret;
	}
	
	# is character over-encumbered? ============================================
	function isOverEncumbered($actor)
	{
		return $this->_overEncumbered($actor, $this->getEncumbrance($actor));
	}
	
	# would character be overencumbered if x encumbrance was added? ============
	function wouldBeOverEncumbered($actor, $weight)
	{
		return $this->_overEncumbered($actor, $this->getEncumbrance($actor) + $weight);
	}
	
	private function _overEncumbered($actor, $enc)
	{
		if($this->hasSkill("packmule", $actor))
		{
			if($enc > 75)
				return true;
		}
		else if($enc > 60)
			return true;	
		
		return false;
	}
	
	# move actor to map, x, y ==================================================
	function move(&$actor, $map, $x, $y)
	{
		$sql = 0;
		$query = 0;
		$this->ci->load->model('map');
		$oldcell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		$cell = $this->ci->map->getCellInfo($map, $x, $y);
		if(! $cell) return;
		$msg = array();
		if($this->isOverEncumbered($actor['actor']))
			return array("Your encumbrance prevents you from moving further.");
		if($actor['stat_ap'] < 1) return;
		$triggers = $this->ci->map->getCellLeaveTriggers($actor['map'],
			$actor['x'], $actor['y'], $actor['indoors']);
		
		foreach($triggers as $t)
		{
			$which = "c_{$t}";
			$this->ci->load->model("classes/cell/{$which}");
			$res = call_user_func(array($this->ci->$which, 'leave'), $actor);
			if($res[0] === false) return $res[1];
			foreach($res[1] as $r) $msg[] = $r;
		}
		
		$triggers = $this->ci->map->getCellArriveTriggers($map, $x, $y,
			$actor['indoors']);

		if(! $triggers)
		{
			$res = $this->spendAP(1, $actor);
			foreach($res as $r) $msg[] = $r;
		}
		
		foreach($triggers as $t)
		{
			$which = "c_{$t}";
			$this->ci->load->model("classes/cell/{$which}");
			$res = call_user_func(array($this->ci->$which, 'arrive'), $actor);
			if($res[0] === false) return $res[1];
			foreach($res[1] as $r) $msg[] = $r;
		}
		
		if($enc > 50)
		{
			$msg[] = "Your encumbrance makes movement more difficult.";
			$this->incStat('ap', -1, $actor['actor']);
		}
		
		# get effect_move triggers
		$sql = <<<SQL
			select abbrev from actor_effect ae
			join effect_trigger et on ae.effect = et.effect
			join effect e on e.effect = ae.effect
			where actor = ? and et.move = b'1'
SQL;
		$query = $this->db->query($sql, array($actor['actor']));
		
		# fire effect-move triggers
		if($query->num_rows() > 0)
		{
			$this->ci->load->model('effects');
			$res = $query->result_array();
			$where = array('map' => $map, 'x' => $x, 'y' => $y,
				'i' => $actor['indoors']);
			
			foreach($res as $r)
			{
				$which = "e_{$r['abbrev']}";
				$this->ci->load->model('effects/' . $which);
				$ret = call_user_func(array($this->ci->$which, "move"), &$where,
					$actor);
				foreach($ret as $rr) $msg[] = $rr;
			}
		}
		
		if($map == false) $map = $actor['map'];
		$sql = 'update actor set map = ?, x = ?, y = ? where actor = ?';
		$this->db->query($sql, array($map, $x, $y, $actor['actor']));
		return $msg;
	}

	# move actor indoors/outdoors ==============================================
	function setIndoors($i = 1, $actor)
	{
		$sql = <<<SQL
			select building from actor a
			left join map_cell mc on a.map = mc.map and a.x = mc.x
				and a.y = mc.y
			where actor = ? and building is not null
SQL;
		$query = $this->db->query($sql, array($actor));
		if($query->num_rows() == 0) return false;
		$sql = 'update actor set indoors = ? where actor = ?';
		$query = $this->db->query($sql, array($i, $actor));
		$msg = array();
		$sql = <<<SQL
			select abbrev from actor_effect ae
			join effect e on ae.effect = e.effect
			join effect_trigger et on ae.effect = et.effect
			where ae.actor = ? and et.move = b'1'
SQL;
		$q = $this->db->query($sql, array($actor));
		$who = $this->getInfo($actor);
		$this->ci->load->model('effects');
		$res = $q->result_array();
		$where = 
			array('map' => $who['map'], 'x' => $who['x'], 'y' => $who['y'],
				'i' => $i);
			
		foreach($res as $r)
		{
			$which = "e_{$r['abbrev']}";
			$this->ci->load->model('effects/' . $which);
			$ret = call_user_func(array($this->ci->$which, "move"), &$where,
				&$who);
			foreach($ret as $rr) $msg[] = $rr;
		}
		
		return $msg;
	}
	
	# increment/decrement actor stat ===========================================
	function incStat($stat, $inc, $actor)
	{
		$sql = <<<SQL
			update actor set
				stat_{$stat} = stat_{$stat} + {$inc}
			where actor = ?
SQL;
		$query = $this->db->query($sql, array($actor));
	}
	
	# set actor stat ===========================================================
	function setStat($stat, $val, $actor)
	{
		$sql = 'update actor set stat_' . $stat . ' = ? where actor = ?';
		$query = $this->db->query($sql, array($val, $actor));
	}
	
	# equip an item ============================================================
	function equipItems($instance, &$actor)
	{
		$sql = <<<SQL
			select eq_type from actor_item ai
			left join item i on i.inum = ai.inum
			where instance = ? and eq_type is not null
			and (ai.durability > 0 or ai.durability is null)
SQL;
		$query = $this->db->query($sql, array($instance));
		if($query->num_rows() <= 0) return false;
		$item = $query->row_array();
		$where = '';
		$msg = array();
		
		switch($item['eq_type'])
		{
			# weapons
			case '1H':
			case '2H':
			{
				$where = "where (eq_slot = 'MH' or eq_slot = 'OH')";
				break;
			}
			# armor
			default:
			{
				$where = "where eq_slot = '{$item['eq_type']}'";
				break;
			}
		}
		
		$sql = <<<SQL
			select eq_slot, eq_type from actor_item ii
			join item i on i.inum = ii.inum
			{$where}
			and actor = ?
SQL;
		$query = $this->db->query($sql, array($actor['actor']));
		$eq = $query->result_array();
		$cnt = count($eq);
		$slot = '';
		
		switch($item['eq_type'])
		{
			# weapons
			case '1H':
			{
				if($cnt >= 2) return false;
				$slot = 'MH';
				
				foreach($eq as $e)
				{
					if($e['eq_type'] == '2H')
						return array("You don't have a free hand.");
					if($e['eq_slot'] == 'MH') $slot = 'OH';
				}
				
				break;
			}
			case '2H':
			{
				if($cnt >= 1) return array("You don't have a free hand.");
				$slot = 'MH';
				break;
			}
			# armor
			default:
			{
				if($cnt >= 1) return array(
					"You are already wearing armor on that part of your body.");
				$slot = $item['eq_type'];
				break;
			}
		}
		
		# item equip trigger
		$sql = <<<SQL
			select abbrev from actor_item ai
				join item i on ai.inum = i.inum
				join item_trigger it on ai.inum = it.inum
				where ai.instance = ? and eq_slot is null and it.equip = b'1'
			union
			select abbrev from actor_item ai
				join item_class ic on ai.inum = ic.inum
				join class_item_trigger cit on ic.iclass = cit.iclass
				where ai.instance = ? and eq_slot is null and cit.equip = b'1'
SQL;
		$query = $this->db->query($sql, array($instance, $instance));
		
		if($query->num_rows() > 0)
		{
			$r = $query->result_array();
			
			foreach($r as $row)
			{
				$which = "i_{$row['abbrev']}";		
				$this->ci->load->model("items/{$which}");
				$res = call_user_func(array($this->ci->$which, 'equip'),
					&$actor, &$instance);
				foreach($res as $r) $msg[] = $r;		
			}
		}
		
		if($instance == 0) return $msg;
		$sql = <<<SQL
			update actor_item set eq_slot = ?
			where instance = ? and actor = ?
SQL;
		$this->db->query($sql, array($slot, $instance, $actor['actor']));
		
		if($this->db->affected_rows() <= 0)
		{
			$msg[] = 'Error equiping item.';
			return $msg;
		}
		
		$msg[] = 'Item equipped.';
		$ret = $this->spendAP(1, &$actor);
		foreach($ret as $r) $msg[] = $r;		
		return $msg;
	}

	# remove an item ===========================================================
	function removeItems($instance, &$actor)
	{
		$this->ci->load->model('item');
		$item = $this->ci->item->describe($instance, true);
		$sql = <<<SQL
			select i.abbrev from actor_item ai
				join item i on ai.inum = i.inum
				join item_trigger it on it.inum = ai.inum
				where ai.instance = ? and eq_slot is not null
					and it.remove = b'1'
			union
			select ci.abbrev from actor_item ai
					join item_class ic on ai.inum = ic.inum
					join class_item ci on ic.iclass = ci.iclass
					join class_item_trigger cit on ic.iclass = cit.iclass
					where ai.instance = ? and eq_slot is not null
						and cit.remove = b'1'
SQL;
		$query = $this->db->query($sql, array($instance, $instance));
		$msg = array();
		
		if($query->num_rows() > 0)
		{
			$r = $query->result_array();
			
			foreach($r as $row)
			{
				$which = "i_{$row['abbrev']}";
				$this->ci->load->model("items/{$which}");
				$ret = call_user_func(array($this->ci->$which, 'remove'), &$actor,
					&$instance);
				foreach($ret as $r) $msg[] = $r;
			}
		}
		
		if($instance == 0) return $msg;
		$sql = <<<SQL
			update actor_item set eq_slot = NULL
			where instance = ? and actor = ? and eq_slot is not NULL
SQL;
		$query = $this->db->query($sql, array($instance, $actor['actor']));
		if($this->db->affected_rows() <= 0) return false;
		# make sure we clear ammo if it had any
		$sql = 'delete from actor_item_ammo where instance = ?';
		$query = $this->db->query($sql, array($instance));
		$msg[] = 'Item removed.';
		$ret = $this->spendAP(1, &$actor);
		foreach($ret as $r) $msg[] = $r;
		return $msg;
	}

	# use skill ================================================================
	function useSkill($skill, &$actor, $args = false)
	{
		if(is_numeric($skill))
		{
			$sql = 'select abbrev from skill where skill = ?';
			$query = $this->db->query($sql, $skill);
			if($query->num_rows() <= 0) return array('There is no such skill.');
			$res = $query->row_array();
			$skill = $res['abbrev'];
		}
		
		if(! $this->hasSkill($skill, $actor['actor']))
			return array("You don't know how to do that.");
		$this->ci->load->model('skills/' . $skill);
		if($args === false || count($args) == 0)
			return call_user_func(array($this->ci->$skill, 'fire'), &$actor);
		return call_user_func(array($this->ci->$skill, 'fire'), &$actor, $args);
	}
	
	# attack an actor ==========================================================
	function attack($victim, &$actor)
	{
		if($actor['stat_hp'] <= 0 || $actor['stat_ap'] <= 0)
			return false;
		if($victim == $actor['actor'])
			return false;
		$vic = $this->getInfo($victim);
		
		# not on this cell (or not visible)
		if($vic['actor'] > 0 && ($vic['map'] != $actor['map']
			|| $vic['x'] != $actor['x'] || $vic['y'] != $actor['y']
			|| $vic['indoors'] != $actor['indoors']
			|| ! $this->isVisible($victim)))
		{
			return array('They are not here.');
		}
		
		if($vic['actor'] <= 0)
		{
			$cellinfo = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
				$actor['y']);
				
			if($cellinfo['clan'])
			{
				if($actor['indoors']) return false;
				$this->ci->load->model('clan');
				$shield =
					$this->ci->clan->getStrongholdShield($cellinfo['clan']);
				if($shield <= 0)
					return array("There is no barrier left to attack.");
			}
			else
				return false;
		}
		
		$msg = array();
		$totdmg = 0;
		$weps = $this->getWeapons($actor['actor']);
		$this->ci->load->model('effects');
		
		# cycle through attacker's weapons
		foreach($weps as $w)
		{
			$swing = array();
			$swing['chance'] = $this->getChanceToHit($actor, $vic);
			$swing['wep'] = $w;
			$swing['target'] = false;
			$swing['crit'] = 1;
			
			# get effect_attack
			$sql = <<<SQL
				select abbrev from actor_effect ae
				join effect_trigger et on ae.effect = et.effect
				join effect e on ae.effect = e.effect
				where actor = ? and et.attack = b'1'
				order by priority desc
SQL;
			$query = $this->db->query($sql, array($actor['actor']));
			
			if($query->num_rows() > 0)
			{
				$res = $query->result_array();
				
				foreach($res as $r)
				{
					$which = "e_{$r['abbrev']}";
					$this->ci->load->model('effects/' . $which);
					$rres = call_user_func(array($this->ci->$which, "attack"),
						&$vic, &$actor, &$swing);
					foreach($rres as $rr) $msg[] = $rr;
				}
			}
			
			# get effect_defend
			$sql = <<<SQL
				select abbrev from actor_effect ae
				join effect_trigger et on ae.effect = et.effect
				join effect e on ae.effect = e.effect
				where actor = ? and et.defend = b'1'
				order by priority desc
SQL;
			$query = $this->db->query($sql, array($vic['actor']));
			
			if($query->num_rows() > 0)
			{	
				$res = $query->result_array();
				
				foreach($res as $r)
				{
					$which = "e_{$r['abbrev']}";
					$this->ci->load->model('effects/' . $which);
					$rres = call_user_func(array($this->ci->$which, "defend"),
						&$vic, &$actor, &$swing);
					foreach($rres as $rr) $msg[] = $rr;
				}
			}
			
			$ret = $this->attackWith(&$vic, $swing['wep'], $swing['target'],
				&$swing['chance'], $swing['crit'], &$actor, &$fail, &$hit);
			foreach($ret as $r) $msg[] = $r;
			if($fail) break;
			if(isset($hit['hit']))
				$totdmg += $hit['dmg'] - $hit['absorbed'];
		}
		
		if(! $fail)
		{
			$ret = $this->spendAP(1, &$actor);
			if($ret) foreach($ret as $r) $msg[] = $r;
			$this->setStatFlag($victim);
		}
		
		return $msg;
	}

	# attack utility function ==================================================
	function attackWith(&$vic, $w, $t = false, $o = false, $c = false, &$actor,
		&$fail, &$hit)
	{
		if($vic['actor'] <= 0)
		{
			$cellinfo = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
				$actor['y']);
				
			if($cellinfo['clan'])
			{
				if($actor['indoors']) return false;
				$this->ci->load->model('clan');
				$shield =
					$this->ci->clan->getStrongholdShield($cellinfo['clan']);
				if($shield <= 0)
					return array("There is no barrier left to attack.");
			}
			else
				return false;
		}
		
		$fail = true;
		
		# can we reach?
		if($this->isElevated($vic['actor'])
			!= $this->isElevated($actor['actor'])
			&& $w['distance'] == 'melee')
		{
			return array('You cannot reach them from here.');
		}			
		
		# do we have ammo?		
		if($w['distance'] != 'melee' && ! isset($w['no_ammo']))
		{
			$sql = <<<SQL
				select ai.instance, count(1) as cnt, ifnull(dmg, 0) as dmg
					from actor_item ai
				join actor_item_ammo aim on aim.ammo = ai.inum
				left join item_ammo ia on ai.inum = ia.inum
				where aim.instance = ? and actor = ?
				group by ai.inum
SQL;
			$query = $this->db->query($sql, array($w['instance'],
				$actor['actor']));
			$res = $query->row_array();
			if($res['cnt'] <= 0)
				return array('Your weapon needs ammunition!');
			$this->dropItems(array($res['instance']), $actor['actor']);
			$swing['wep']['dmg_min'] += $res['dmg'];
			$swing['wep']['dmg_max'] += $res['dmg'];
		}
		
		# is our target dead?
		if($vic['actor'] > 0 && $vic['stat_hp'] <= 0)
			return array(
				'You massacre their corpse. You feel better about yourself.');
		
		$aslots = array(
			array('head', 1),
			array('arms', 2),
			array('torso', 3),
			array('legs', 2)
			);
		$tot = 0;
		foreach($aslots as $a) $tot += $a[1];
		if($vic['actor'] <= 0) $t = 'barrier';
		$target = $t;
		
		if($t === false)
		{
			$roll = rand(1, $tot);
			$cur = 0;
			$slots = count($aslots);
			
			for($a = 0; $a < $slots; $a++)
			{
				$cur += $aslots[$a][1];
				
				if($roll <= $cur)
				{
					$target = $aslots[$a][0];
					break;
				}
			}
		}
		
		if($o === false) $o = $this->getChanceToHit(&$actor, &$vic);
		if($c === false) $c = 1; # 5% chance to crit
		$succ = rand(1, 20);
		$hit['target'] = $target;
		$hit['wep'] = $w;
		
		# hit!
		if($succ <= $o)
		{
			$hit['hit'] = true;
			# check for opponent's armor
			$armor = $this->getArmor($target, $vic['actor']);
			# crit!
			if($succ == $c)
				$hit['dmg'] = round($w['dmg_max'] * 1.5) + $w['dmg_bonus'];
			else
				$hit['dmg'] = rand($w['dmg_min'], $w['dmg_max']);			
			if(! $this->isVisible($actor['actor'])) $hit['dmg'] *= 2;
			$hit['absorbed'] = $armor[$w['dmg_type']];
			if($hit['dmg'] < $hit['absorbed']) $hit['absorbed'] = $hit['dmg'];
			$hit['dmg'] -= $hit['absorbed'];
			# check for effect_struck records
			$sql = <<<SQL
				select abbrev from actor_effect ae
				join effect_trigger et on ae.effect = et.effect
				join effect e on e.effect = ae.effect
				where actor = ? and et.struck = b'1'
SQL;
			$query = $this->db->query($sql, array($vic['actor']));
			
			if($query->num_rows() > 0)
			{
				$this->ci->load->model('effects');
				$rres = $query->result_array();
				
				foreach($rres as $rr)
				{
					$which = "e_{$rr['abbrev']}";
					$this->ci->load->model('effects/' . $which);
					$mmsg = call_user_func(array($this->ci->$which, "struck"),
						&$vic, &$actor, &$hit);
					foreach($mmsg as $mm) $msg[] = $mm;
				}
			}
			
			# check for effect_hit records
			$sql = <<<SQL
				select abbrev from actor_effect ae
				join effect_trigger et on ae.effect = et.effect
				join effect e on ae.effect = e.effect
				where actor = ? and et.hit = b'1'
SQL;
			$query = $this->db->query($sql, array($actor['actor']));
			
			if($query->num_rows() > 0)
			{
				$this->ci->load->model('effects');
				$rres = $query->result_array();
				
				foreach($rres as $rr)
				{
					$which = "e_{$rr['abbrev']}";
					$this->ci->load->model('effects/' . $which);
					$mmsg = call_user_func(array($this->ci->$which, "hit"),
							&$vic, &$actor, &$hit);
					foreach($mmsg as $mm) $msg[] = $mm;
				}
			}
			
			if($hit['hit'])
			{
				$this->ci->load->model('item');
				
				if($succ > $c)
				{
					$msg[] =
						"You attack {$vic['aname']}'s {$target} with your {$w['iname']} for {$hit['dmg']} {$w['dmg_type']} damage.";
					if($vic['actor'] > 0 && $vic['user'] > 0)
						$this->sendEvent(
							"{$actor['aname']} attacked your {$target} with their {$w['iname']} for {$hit['dmg']} {$w['dmg_type']} damage.",
							$vic['actor']
							);
				}
				else
				{
					$msg[] =
						"You critically strike {$vic['aname']}'s {$target} with your {$w['iname']} for {$hit['dmg']} {$w['dmg_type']} damage!";
					if($vic['actor'] > 0 && $vic['user'] > 0)
						$this->sendEvent(
							"{$actor['aname']} critically struck your {$target} with their {$w['iname']} for {$hit['dmg']} {$w['dmg_type']} damage!",
							$vic['actor']
							);
				}
				
				if($hit['absorbed'] > 0)
				{
					$msg[] =
						"Their armor absorbed {$hit['absorbed']} damage.";
					if($vic['user'] > 0)
						$this->sendEvent("Your armor absorbed {$hit['absorbed']} "
							. "damage.", $vic['actor']);
				}

				if($w['eq_slot'] == 'MH')
					$this->addXP($actor, round($hit['dmg'] / 2));
				else
					$this->addXP($actor, round($hit['dmg'] / 3));
				$this->damage($hit['dmg'], &$vic, &$actor);
				$vic['stat_hp'] -= $hit['dmg'];
				
				# armor durability decrement
				if(rand(1, 100) <= 15 && $vic['user'] > 0)
				{
					$ret = $this->ci->item->decDurability($armor['instance'],
						$vic['actor']);
					foreach($ret as $r)
						$this->sendEvent($r, $vic['actor']);
				}
				
				# weapon durability decrement
				if(rand(1, 100) <= 15)
				{
					$ret = 
						$this->ci->item->decDurability($w['instance'], $actor);
					foreach($ret as $r) $msg[] = $r;
				}			
				
				# victim died
				if($vic['actor'] > 0 && $vic['stat_hp'] <= 0)
				{
					$this->addXP($actor, 5);
					$msg[] = "You have killed {$vic['aname']}!";
					
					if($vic['user'] > 0)
					{
						$this->sendEvent(
							"You have been killed by {$actor['aname']}!",
							$vic['actor']);
						# inventory damage
						$items = $this->getItems($vic['actor']);

						foreach($items as $i)
						{
							if(rand(1, 100) > 35)
								continue;
							$ret = $this->ci->item->decDurability($i['instance'], $vic);
							foreach($ret as $r)
								$this->sendEvent($r, $vic['actor']);
						}
					}
					
					$this->ci->load->model('map');
					$this->ci->map->sendCellEvent(
						"{$vic['aname']} was killed by {$actor['aname']}!",
						array($vic['actor'], $actor['actor']), $actor['map'],
						$actor['x'], $actor['y'], $actor['indoors']);
					$this->ci->map->setRadiusEvtM($actor['map'], $actor['x'],
						$actor['y']);
				}
				
				if($vic['actor'] > 0 && $hit['dmg'] > 0
					&& ! $this->isVisible($actor['actor']))
				{
					$msg[] = 'They never saw it coming!';
					$this->sendEvent('They were hiding in the shadows!',
						$vic['actor']);
				}
			}
		}
		# miss
		else
		{
			$msg[] = 'You missed!';
			
			# get event_miss
			$sql = <<<SQL
				select abbrev from actor_effect ae
				join effect_trigger et on ae.effect = et.effect
				join effect e on e.effect = ae.effect
				where actor = ? and et.miss = b'1'
SQL;
			$query = $this->db->query($sql, array($actor['actor']));
			
			if($query->num_rows() > 0)
			{
				$this->ci->load->model('effects');
				$res = $query->result_array();
				
				foreach($res as $r)
				{
					$which = "e_{$r['abbrev']}";
					$this->ci->load->model('effects/' . $which);
					$rres = call_user_func(array($this->ci->$which, "miss"),
						&$vic, &$actor, &$hit);
					foreach($rres as $rr) $msg[] = $rr;
				}
			}
		}
		
		$fail = false;
		return $msg;
	}
	
	# get an actor's weapons ===================================================
	function getWeapons($actor)
	{
		# get actor's weapons (if any)
		$sql = <<<SQL
			select iname, dmg_min, dmg_max, dmg_bonus, distance, eq_slot,
				dmg_type, eq_type, i.inum, instance
			from actor_item ai
			join item i on ai.inum = i.inum
			join item_weapon w on i.inum = w.inum
			where actor = ? and eq_slot is not null
SQL;
		$query = $this->db->query($sql, array($actor));
		$res = array();
		# barehanded?
		if($query->num_rows() <= 0)
			$res = array(array('iname' => 'fists', 'dmg_min' => 1,
				'dmg_max' => 2, 'dmg_bonus' => 0, 'distance' => 'melee',
				'dmg_type' => 'blunt', 'eq_slot' => 'RH'));
		else
			$res = $query->result_array();
		return $res;
	}
	
	# update actor's map evt flag ==============================================
	function setMapFlag($actor)
	{
		$sql = 'update actor set evtm = 1 where actor = ?';
		$query = $this->db->query($sql, array($actor));
		if($this->db->affected_rows() <= 0) return false;
		return true;
	}
	
	# update actor's status evt flag ===========================================
	function setStatFlag($actor)
	{
		if(is_array($actor))
		{
			foreach($actor as $a)
				$this->setStatFlag($a);
			return true;
		}
		
		$sql = 'update actor set evts = 1 where actor = ?';
		$query = $this->db->query($sql, array($actor));
		if($this->db->affected_rows() <= 0) return false;
		return true;
	}
		
	# clear actor's event flags ================================================
	function clearFlags($actor)
	{
		$sql = 'update actor set evts = 0, evtm = 0 where actor = ?';
		$query = $this->db->query($sql, array($actor));
		if($this->db->affected_rows() <= 0) return false;
		return true;
	}
	
	# set actor's last action counter ==========================================
	function setLast($actor)
	{
		$sql = 'update actor set last = ' . time() . ' where actor = ?';
		$query = $this->db->query($sql, array($actor));
		if($this->db->affected_rows() <= 0) return false;
		return true;
	}
	
	# get an actor's armor information =========================================
	function getArmor($slot, $actor)
	{
		$sql = <<<SQL
			select ifnull(piercing, 0) as piercing,
				ifnull(slashing, 0) as slashing,
				ifnull(blunt, 0) as blunt,
				instance, i.iname
			from actor_item ai
			join item i on ai.inum = i.inum
			left join item_armor ia on ai.inum = ia.inum
			where actor = ? and lower(eq_slot) = lower(?)
SQL;
		$query = $this->db->query($sql, array($actor, $slot));
		if($query->num_rows() <= 0) return false;
		$armor = $query->row_array();
		
		# get armor effect triggers
		$sql = <<<SQL
			select abbrev from actor_effect ae
			join effect_trigger et on ae.effect = et.effect
			join effect e on e.effect = ae.effect
			where actor = ? and et.armor = b'1'
SQL;
		$query = $this->db->query($sql, array($actor['actor']));
		
		# fire effect-armor triggers
		if($query->num_rows() > 0)
		{
			$this->ci->load->model('effects');
			$res = $query->result_array();
			
			foreach($res as $r)
			{
				$which = "e_{$r['abbrev']}";
				$this->ci->load->model('effects/' . $which);
				call_user_func(array($this->ci->$which, "armor"), $actor,
					&$armor);
			}
		}
		
		return $armor;
	}
	
	# spend action points voluntarily (to trigger effect.ap_func) ==============
	function spendAP($ap, &$actor)
	{
		if($ap < 0) return false;
		$sql = 'update actor set stat_ap = stat_ap - ? where actor = ?';
		$query = $this->db->query($sql, array($ap, $actor['actor']));
		$sql = <<<SQL
			select abbrev from actor_effect ae
			join effect_trigger et on ae.effect = et.effect
			join effect e on ae.effect = e.effect
			where ae.actor = ? and et.ap = b'1'
SQL;
		$query = $this->db->query($sql, array($actor['actor']));
		if($query->num_rows() <= 0) return false;
		$res = $query->result_array();
		$this->ci->load->model('effects');
		$msg = array();
		
		foreach($res as $r)
		{
			$which = "e_{$r['abbrev']}";
			$this->ci->load->model('effects/' . $which);
			$ret = call_user_func(array($this->ci->$which, "ap"), $ap, $actor);
			foreach($ret as $rr) $msg[] = $rr;
		}
		
		return $msg;
	}
	
	# spend magic points =======================================================
	function spendMP($mp, &$actor)
	{
		if($mp < 0) return false;
		$s = 'update actor set stat_mp = stat_mp - ? where actor = ?';
		$this->db->query($s, array($mp, $actor['actor']));
	}
	
	# add an effect ============================================================
	function addEffect($effect, &$actor)
	{
		$abbrev = $effect;
		
		if(is_numeric($effect))
		{
			$sql = 'select abbrev from effect where effect = ?';
			$query = $this->db->query($sql, array($effect));
			if($query->num_rows() <= 0) return false;
			$r = $query->row_array();
			$abbrev = $r['abbrev'];
		}
		else
		{
			$sql = 'select effect from effect where abbrev = ?';
			$query = $this->db->query($sql, array($abbrev));
			if($query->num_rows() <= 0) return false;
			$r = $query->row_array();
			$effect = $r['effect'];
		}
		
		$sql = 'insert ignore into actor_effect (effect, actor) values (?, ?)';
		$this->db->query($sql, array($effect, $actor['actor']));
		if($this->db->affected_rows() <= 0) return false;		
		$this->setStatFlag($actor['actor']);
		
		# check for model
		$s = "select abbrev from effect where effect = ? and `on` = b'1'";
		$q = $this->db->query($s, $effect);
		$msg = array();
		
		if($q->num_rows() > 0)
		{
			$r = $q->result_array();
			
			foreach($r as $row)
			{
				$which = "e_{$row['abbrev']}";
				$this->ci->load->model("effects/{$which}");
				$ret = call_user_func(array($this->ci->$which, "on"), &$actor);
				foreach($ret as $rr) $msg[] = $rr;
			}
		}
		
		return $msg;
	}
	
	# remove an effect =========================================================
	function removeEffect($effect, &$actor)
	{
		$who = $actor;
		if (is_array($actor)) $who = $actor['actor'];
		
		if($effect == 'all')
		{
			$sql = <<<SQL
				select ae.effect as effect from actor_effect ae
				join effect e on ae.effect = e.effect
				where actor = ? and persist = 0
SQL;
			$query = $this->db->query($sql, array($who));
			if($query->num_rows() <= 0) return false;
			$res = $query->result_array();
			$msg = array();
			
			foreach($res as $r)
			{
				$ret = $this->removeEffect($r['effect'], &$actor);
				foreach($ret as $r) $msg[] = $r;
			}
			
			return $msg;
		}
		
		$abbrev = $effect;
		
		if(is_numeric($effect))
		{
			$sql = 'select abbrev from effect where effect = ?';
			$q = $this->db->query($sql, array($effect));
			if($q->num_rows() <= 0) return false;
			$r = $q->row_array();
			$abbrev = $r['abbrev'];
		}
		else
		{
			$sql = 'select effect from effect where abbrev = ?';
			$q = $this->db->query($sql, array($abbrev));
			if($q->num_rows() <= 0) return false;
			$r = $q->row_array();
			$effect = $r['effect'];
		}
		
		$sql = 'delete from actor_effect where actor = ? and effect = ?';
		$this->db->query($sql, array($who, $effect));
		if($this->db->affected_rows() <= 0) return false;
		# check for model
		$s = "select abbrev from effect where effect = ? and `off` = b'1'";
		$q = $this->db->query($s, $effect);
		$msg = array();
		
		if($q->num_rows() > 0)
		{
			$r = $q->result_array();
			
			foreach($r as $row)
			{
				$which = "e_{$row['abbrev']}";
				$this->ci->load->model("effects/{$which}");
				$ret = call_user_func(array($this->ci->$which, "off"), &$actor);
				foreach($ret as $rr) $msg[] = $rr;
			}
		}
		
		return $msg;
	}
	
	# get effects ==============================================================
	function getEffects($actor, $hidden = false)
	{
		$extra = 'and e.hide = 0';
		if($hidden == true) $extra = '';
		$sql = <<<SQL
			select e.effect as effect, abbrev, ename from actor a
			join actor_effect ae on a.actor = ae.actor
			join effect e on ae.effect = e.effect {$extra}
			where a.actor = ?
			order by lower(ename) asc
SQL;
		$query = $this->db->query($sql, array($actor));
		if($query->num_rows() <= 0) return false;
		return $query->result_array();
	}
	
	# check if has effect ======================================================
	function hasEffect($effect, $actor)
	{
		$sql = '';
		
		if(is_numeric($effect))
		{
			$sql = <<<SQL
				select 1 from actor a
				join actor_effect ae on a.actor = ae.actor
				join effect e on ae.effect = e.effect
				where a.actor = ? and e.effect = ?
SQL;
		}
		else
		{
			$sql = <<<SQL
				select 1 from actor a
				join actor_effect ae on a.actor = ae.actor
				join effect e on ae.effect = e.effect
				where a.actor = ? and lower(e.abbrev) = lower(?)
SQL;
		}
		
		$query = $this->db->query($sql, array($actor, $effect));
		if($query->num_rows() > 0) return true;
		return false;
	}
	
	# check if has effect (fuzzy) ==============================================
	function hasEffectLike($effect, $actor)
	{
		$sql = <<<SQL
			select 1 from actor a
			join actor_effect ae on a.actor = ae.actor
			join effect e on ae.effect = e.effect
			where a.actor = ? and lower(e.abbrev) like lower(?)
SQL;
		$query = $this->db->query($sql, array($actor, $effect));
		if($query->num_rows() > 0) return true;
		return false;
	}
	
	# take damage ==============================================================
	function damage($dmg, &$vic, &$actor = false)
	{		
		if($dmg <= 0) return false;
		if($actor === false)
			$actor = $this->getInfo($this->session->userdata('actor'));
		
		if($vic['actor'] <= 0)
		{
			$cellinfo = $this->ci->map->getCellInfo($actor['map'],
				$actor['x'], $actor['y']);
			if(! $cellinfo['clan']) return false;
			$this->ci->load->model('clan');
			$this->ci->clan->decStrongholdShield($cellinfo['clan'], $dmg);
			return;
		}
		
		$sql = 'update actor set stat_hp = stat_hp - ? where actor = ?';
		$this->db->query($sql, array($dmg, $vic['actor']));
		if($this->db->affected_rows() <= 0)
			return false;
		#TODO: effect_damage
		if($dmg >= $vic['stat_hp'])
			$this->removeEffect('all', &$vic);
	}
	
	# heal =====================================================================
	function heal(&$actor, &$victim, &$heal)
	{
		$sql = <<<SQL
			select abbrev from actor_effect ae
			join effect_trigger et on ae.effect = et.effect
			join effect e on ae.effect = e.effect
			where ae.actor = ? and et.heal = b'1'
SQL;
		$query = $this->db->query($sql, array($actor['actor']));
		$msg = array();
		
		if($query->num_rows() > 0)
		{
			$res = $query->result_array();
			$this->ci->load->model('effects');
			
			foreach($res as $r)
			{
				$which = "e_{$r['abbrev']}";
				$this->ci->load->model('effects/' . $which);
				$rres = call_user_func(array($this->ci->$which, "heal"),
					&$victim, &$actor, &$heal);
				foreach($rres as $rr) $msg[] = $rr;
			}
		}
		
		$sql = <<<SQL
			select abbrev from actor_effect ae
			join effect_trigger et on ae.effect = et.effect
			join effect e on ae.effect = e.effect
			where ae.actor = ? and et.healed = b'1'
SQL;
		$query = $this->db->query($sql, array($victim['actor']));
		
		if($query->num_rows() > 0)
		{
			$res = $query->result_array();
			$this->ci->load->model('effects');
			
			foreach($res as $r)
			{
				$which = "e_{$r['abbrev']}";
				$this->ci->load->model('effects/' . $which);
				$rres = call_user_func(array($this->ci->$which, "healed"),
					&$victim, &$actor, &$heal);
				foreach($rres as $rr) $msg[] = $rr;
			}
		}
		
		$this->incStat('hp', $heal['hp'], $victim['actor']);
		if($victim['actor'] != $actor['actor'])
			$this->addXP($actor, round($heal['hp'] / 3));
		return $msg;
	}
	
	# check for skill ==========================================================
	function hasSkill($skill, $actor)
	{
		$field = 's.abbrev';		
		if(is_numeric($skill))
			$field = 'acts.skill';
		$sql = <<<SQL
			select abbrev, sname from actor a
			join actor_skill acts on a.actor = acts.actor
			join skill s on acts.skill = s.skill
			where a.actor = ? and {$field} = ?
SQL;
		$query = $this->db->query($sql, array($actor, $skill));
		if($query->num_rows() > 0) return true;
		return false;
	}
	
	# is the actor visible? check effects ======================================
	function isVisible($actor)
	{
		$sql = <<<SQL
			select count(1) as cnt from actor_effect ae
			join effect_hide eh on ae.effect = eh.effect
			where ae.actor = ?
SQL;
		$query = $this->db->query($sql, array($actor));
		$res = $query->row_array();
		if($res['cnt'] > 0) return false;
		return true;
	}
	
	# load weapon ==============================================================
	function loadWeapon($weapon, $ammo, $actor)
	{
		$instance = $this->getInstanceOf($ammo, $actor);
		if($instance === false) return ('Invalid ammo selection.');
		$sql = 'select 1 from actor_item where actor = ? and instance = ?';
		$query = $this->db->query($sql, array($actor, $weapon));
		if($query->num_rows() <= 0)
			return array("You do not possess that weapon.");
		$sql = <<<SQL
			delete from actor_item_ammo
			where instance in
				(select ai.instance from actor_item ai where actor = ?
				and ai.instance = ? and eq_slot = 'MH')
SQL;
		$this->db->query($sql, array($actor, $weapon));
		$sql = <<<SQL
			select 1 from actor_item
			where (instance = ? or instance = ?) and actor = ?
SQL;
		$query = $this->db->query($sql, array($weapon, $instance, $actor));
		if($query->num_rows() < 2) return array('You are out of ammo.');
		$sql = <<<SQL
			insert ignore into actor_item_ammo (instance, ammo) values (?, ?)
SQL;
		$this->db->query($sql, array($weapon, $ammo));
		return array('Weapon loaded.');
	}
	
	# get instance of item in actor's inventory ================================
	function getInstanceOf($item, $actor, $number = 1, $notequip = false)
	{
		$extra = '';
		if($notequip !== false) $extra = 'and eq_slot is null';
		$sql = <<<SQL
			select instance from item i
			join actor_item ai on i.inum = ai.inum
			where i.inum = ? and actor = ? {$extra}
			limit ?
SQL;
		$query = $this->db->query($sql, array($item, $actor, $number));
		if($query->num_rows() <= 0) return false;
		$res = $query->result_array();
		if($number == 1)
			return $res[0]['instance'];			
		$ret = array();
		foreach($res as $r)
			$ret[] = $r['instance'];
		return $ret;
	}
	
	# get chance to hit ========================================================
	function getChanceToHit(&$actor, &$victim)
	{
		# chance to hit against inanimate objects = 95%
		if($victim['actor'] <= 0) return 19;
		$chance = 3;
		# get elevations of victim and attacker
		$oe = $this->isElevated($actor['actor']);
		$ce = $this->isElevated($victim['actor']);
		if($oe && ! $ce)
			$chance += 2; # +10% to hit when above someone
		else if(! $oe && $ce)
			$chance -= 2; # -10% to hit when below someone
		$sql = <<<SQL
			select abbrev from actor_effect ae
			join effect_trigger et on ae.effect = et.effect
			join effect e on ae.effect = e.effect
			where actor = ? and et.chancetohit = b'1'
SQL;
		$query = $this->db->query($sql, array($actor['actor']));
		
		if($query->num_rows() > 0)
		{
			$res = $query->result_array();
			$this->ci->load->model('effects');
			
			foreach($res as $r)
			{
				$which = "e_{$r['abbrev']}";
				$this->ci->load->model('effects/' . $which);
				$chance += call_user_func(array($this->ci->$which,
					"chancetohit"), &$actor, &$victim);
			}
		}
		
		return $chance;
	}
	
	# is actor elevated? =======================================================
	function isElevated($actor)
	{
		$sql = <<<SQL
			select 1 from actor_effect ae
			join effect_elevated ee on ae.effect = ee.effect
			where actor = ?
SQL;
		$query = $this->db->query($sql, array($actor));
		if($query->num_rows() > 0) return true;
		return false;
	}
	
	# determine actor's user acct ==============================================
	function getUser($actor)
	{
		$sql = 'select user from actor where actor = ?';
		$query = $this->db->query($sql, array($actor));
		$res = $query->row_array();
		return $res['user'];
	}

	function addXP($actor, $xp)
	{
		$actor = $this->getInfo($actor['actor']);
		$s = 'update actor set stat_xp = ? where actor = ?';
		$this->db->query($s, array(min($actor['stat_xplevel'], $actor['stat_xp'] + $xp), $actor['actor']));
		if($this->db->affected_rows() <= 0)
			return false;
		return true;
	}
}
