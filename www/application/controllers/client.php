<?php if(! defined('BASEPATH')) exit();

class Client extends CI_Controller
{
	private $ret_val;
	private $who;
	
	function __construct()
	{
		parent::__construct();
		$this->output->set_header(
			"Cache-Control: no-store, no-cache, must-revalidate");
		#$this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
		#$this->output->set_header("Pragma: no-cache"); 
		
		if(file_exists($this->config->item('maintfile')))
		{
			$this->ret_val = array('maint' => 1);
			die();
		}

		$this->load->library('session');
		if($this->session->userdata('user') === false)
			return $this->ret_val['msg'] = array($this->_logText(
				'Your session is expired. Please login again.'));
		else if($this->session->userdata('actor') === false)
			return $this->ret_val['msg'] = array($this->_logText(
				'No active character. Please choose a character.'));
		$this->load->model('actor');
		$this->load->model('map');
		$this->who = $this->actor->getInfo($this->session->userdata('actor'));
		
		if(in_array($this->uri->segment(2),
			array('chat', 'action', 'attack', 'skill', 'item', 'repeat')))
		{
			$mt = microtime(true);
			
			if($mt - $this->session->userdata('last_action') < 1)
			{
				$this->ret_val = array('msg' => array(
					$this->_logText('&laquo;Throttled&raquo;')));
				die();
			}
			
			$this->session->set_userdata('last_action', $mt);
		}
		
		$msgs = $this->actor->getEvents($this->who['actor']);
		foreach($msgs as $m)
	 		$this->ret_val['msg'][] = $this->_logText($m['descr'], $m['stamp']);
	}
	
	# destructor - output json-encoded return value ============================
	function __destruct()
	{
		if(is_array($this->ret_val['msg']) && ! isset($this->ret_val['msg'][0]))
			unset($this->ret_val['msg']);
		if(in_array($this->uri->segment(2), array('actionview')))
			die();		
		if($this->ret_val)
			echo json_encode($this->ret_val);
	}

	# check character's status =================================================
	function status($force = false, $forcemap = false)
	{
		$this->load->model('actor');
		if($force || $forcemap)
			$this->who = $this->actor->getInfo($this->who['actor']);
		$this->actor->clearFlags($this->who['actor']);
		# char has no AP
		if($this->who['stat_ap'] <= 0)
			return $this->ret_val['stat'] = $this->who;
		
		# check for events
		if(! $this->who['evts'] && ! $force)
		{
			if($this->who['evtm'] == 1 || $forcemap) $this->_map();
			return;
		}
		
		$this->load->model('action');
		
		# char is dead
		if($this->who['stat_hp'] <= 0)
		{
			$dactions = $this->action->getDead($this->who);
			if($dactions) $this->ret_val['actd'] = $dactions;
			return $this->ret_val['stat'] = $this->who;			
		}
		
		$elev = $this->map->cellHasClass('scan', $this->who['map'],
			$this->who['x'], $this->who['y'], $this->who['indoors'], $info);
		
		if($info)
		{
			$this->ret_val['info'] = $info;
			
			if($info['clan'])
			{
				$this->load->model('clan');
				$clan = $this->clan->getInfo($info['clan']);
				$clan = $clan['descr'];
				$this->ret_val['surr'] .=
					"This building is the stronghold of <a href='#' class='clan-link'><i>{$info['clan']}</i>{$clan}</a>. ";
					
				if(! $this->who['indoors'])
				{
					$shield = $this->clan->getStrongholdShield($info['clan']);
					
					if($shield > 0)
					{
						if($this->ret_val['surr'])
							$this->ret_val['surr'] .= "It";
						else
							$this->ret_val['surr'] .= "This building";
						$this->ret_val['surr'] .=
							" is <b>protected</b> by some sort of metaphysical barrier. ";
					}
				}
			}
			
			if($elev) $this->ret_val['info']['elev'] = 1;
			$binfo = $this->map->buildingInfo($this->who['map'],
				$info['building']);
			
			if($this->who['indoors'])
			{
				if($binfo['owner_name'])
				{
					if($this->ret_val['surr'])
						$this->ret_val['surr'] .= "It";
					else
						$this->ret_val['surr'] = "This building";
					$this->ret_val['surr'] .= " is owned by <b>{$binfo['owner_name']}</b>. ";
				}
			}
			
			$bcond = false;
			
			if(array_key_exists('hp', $binfo) && $binfo['hp'])
				switch(true)
				{
					case($binfo['hp'] == 120):
					{
						$bcond = "perfect";
						break;
					}
					case($binfo['hp'] >= 100):
					{
						$bcond = "very good";
						break;
					}
					case($binfo['hp'] >= 80):
					{
						$bcond = "good";
						break;
					}
					case($binfo['hp'] >= 60):
					{
						$bcond = "fair";
						break;
					}
					case($binfo['hp'] >= 40):
					{
						$bcond = "poor";
						break;
					}
					case($binfo['hp'] >= 20):
					{
						$bcond = "bad";
						break;
					}
					case($binfo['hp'] >= 0):
					{
						$bcond = "terrible";
						break;
					}
				}
			
			if($bcond)
			{
				if($this->ret_val['surr'])
					$this->ret_val['surr'] .= "It";
				else
					$this->ret_val['surr'] .= "This building";
				$this->ret_val['surr'] .= " is in <b>{$bcond}</b> condition. ";
			}
		}
		
		$gactions = $this->action->getGlobals($this->who);
		if($gactions) $this->ret_val['actg'] = $gactions;
		$cell = $this->map->getCellInfo($this->who['map'], $this->who['x'],
			$this->who['y']);
		$bactions = $this->action->getBuilding($this->who, $this->who['map'],
			$cell['building']);
		if($bactions) $this->ret_val['actb'] = $bactions;
		$cactions = $this->action->getCellActions($this->who);
		if($cactions) $this->ret_val['actc'] = $cactions;
		$occupants = $this->map->getCellOccupants($this->who['map'],
			$this->who['x'], $this->who['y'], $this->who['indoors']);
			
		if($occupants !== false)
		{
			$this->ret_val['occ'] = array();
			$this->load->model('clan');
			
			foreach($occupants as $occupant)
			{
				if($this->clan->isAllyOf($this->who['clan'], $occupant['clan']))
					$occupant['ally'] = 1;
				else if($this->clan->isEnemyOf($this->who['clan'],
					$occupant['clan']))
				{
					$occupant['enemy'] = 1;
				}
				
				$this->ret_val['occ'][] = $occupant;
			}
		}
		
		$corpses = $this->map->getCellCorpses($this->who['map'],
			$this->who['x'], $this->who['y'], $this->who['indoors']);
		if($corpses !== false) $this->ret_val['corpses'] = $corpses;
		$skills = $this->actor->getSkills($this->who, 0);
		
		# check for construction
		if($this->map->tileIsUnderConstruction($this->who['map'],
			$cell['building']))
		{
			$struct = $this->map->siteStructureName($this->who['map'],
				$cell['building']);
			$needs = $this->map->buildingNeeds($this->who['map'],
				$cell['building']);
			$this->ret_val['surr'] .=
				"A(n) <b>{$struct}</b> is being constructed here. It needs: {$needs}. ";
		}
		
		# get other surroundings
		$surr = $this->map->getSurroundings($this->who, $this->who['map'],
			$this->who['x'], $this->who['y'], $this->who['indoors']);
		foreach($surr as $s)
			$this->ret_val['surr'] .= "{$s} ";
		
		# pull skills
		if($skills)
		{
			$this->ret_val['skills'] = array();
			
			foreach($skills as $s)
			{
				$skill = $s['abbrev'];
				
				try {
					$this->load->model('skills/' . $skill);
					if($this->$skill->show($this->who))
					{
						$this->ret_val['skills'][] = array(
							'abbrev'	=> $s['abbrev'],
							'skill'		=> $s['skill'],
							'sname' 	=> $s['sname'],
							'cost_ap'	=> $s['cost_ap'],
							'cost_mp'	=> $s['cost_mp'],
							'params' 	=> $s['params'],
							'rpt'		=> $s['rpt'],
							'js'		=> $s['js']
							);
					}
				} catch(Exception $e) { }
			}
		}
		
		# pull effects
		$effects = $this->actor->getEffects($this->who['actor']);
		
		if($effects)
		{
			$this->ret_val['effects'] = array();
			
			foreach($effects as $e)
			{
				$cur = array();
				$which = "e_{$e['abbrev']}";
				$this->load->model('effects/' . $which);
				# dynamic display
				if(method_exists($this->$which, 'disp')) {
					$res = $this->$which->disp($this->who);
					if($res) $e['ename'] = $res;
				}

				$this->ret_val['effects'][] = $e;
			}
		}
		
		$this->ret_val['elev'] =
			(int) $this->actor->isElevated($this->who['actor']);
		$this->ret_val['stat'] = $this->who;
		if ($this->who['evtm'] == 1 || $forcemap) $this->_map();
	}

	# get minimap ==============================================================
	function minimap()
	{
		$im = $this->map->getGif($this->who['map'], $this->who['x'],
			$this->who['y'], 8, 12);
		$id = new ImagickDraw();
		$id->setFillOpacity(0.0);
		$id->setStrokeColor(new ImagickPixel('red'));
		$id->rectangle(8 * 12, 8 * 12, 9 * 12, 9 * 12);
		$im->drawImage($id);
		$this->output->set_header('Content-Type: image/gif');
		echo $im;
	}
	
	# get map for character ====================================================
	function _map()
	{
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0) return;
		$cell = $this->map->getCellInfo($this->who['map'], $this->who['x'],
			$this->who['y'], $this->who['indoors']);
		$map = $this->map->getMap($this->who['map'], $this->who['x'],
			$this->who['y'], $this->who['indoors'], $cell['dense'], 2);
		$fmap = array();
		$c = 0;
		
		for($a = $this->who['x'] - 2; $a <= $this->who['x'] + 2; $a++)
			for($b = $this->who['y'] - 2; $b <= $this->who['y'] + 2; $b++)
				if(! isset($map[$c]) || $map[$c]['x'] != $a
					|| $map[$c]['y'] != $b)
				{
					$fmap[] = array('x' => 0, 'y' => 0, 'img' => '',
						'descr' => '', 'occ' => 0);
				}
				else
					$fmap[] = $map[$c++];
		
		$this->ret_val['cells'] = $fmap;
	}

	# move character to x, y, map ==============================================
	function move($x, $y)
	{
		$this->actor->setLast($this->who['actor']);
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0
			|| ($this->who['x'] == $x && $this->who['y'] == $y)) return;
		$map = $this->who['map'];
		$cx = $this->who['x'];
		$cy = $this->who['y'];
		# prevent moving more than one space
		if(abs($cx - $x) > 1 || abs($cy - $y) > 1) return;
		# prevent moving building-to-building (for now)
		$cur = $this->map->getCellInfo($map, $cx, $cy);
		$mov = $this->map->getCellInfo($map, $x, $y);
		if($this->who['indoors'] == 1 && $cur['building'] != $mov['building'])
			return;
		$this->map->setRadiusEvtM($map, $cx, $cy);
		$ret = $this->actor->move($this->who, $map, $x, $y);
		$this->map->setRadiusEvtM($map, $x, $y);
		foreach($ret as $r) $this->ret_val['msg'][] = $this->_logText($r);
		$this->status(1, 1);
	}
	
	# drop item(s) =============================================================
	function drop($items)
	{
		$this->load->model('actor');
		$this->actor->setLast($this->who['actor']);
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0)
			return;
		$ret = $this->actor->dropItems(split('-', $items), $this->who['actor']);
		$msg = '';
		if($ret === false)
			$msg = 'There was an error dropping the item(s).';
		else
			$msg = 'Item(s) dropped.';
		$this->ret_val['msg'][] = $this->_logText($msg);
		$this->status(true);
	}
	
	# drop stack(s) of items ===================================================
	function dropstack($items)
	{
		$this->actor->setLast($this->who['actor']);
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0)
			return;
		$items = split('-', $items);
		$this->load->model('item');
		
		foreach($items as $item)
		{
			$info = $this->item->getInfo($item);
			
			if($info['stack'] == 0)
				$this->actor->dropItems(array($item), $this->who['actor']);
			else
				do
				{
					$i = $this->actor->getInstanceOf($info['inum'],
						$this->who['actor']);
					
					if($i && ! $this->actor->dropItems(array($i),
						$this->who['actor']))
					{
						return $this->ret_val['msg'][] = $this->_logText(
							'There was an error dropping the stack(s).');
					}
				}
				while($i);
		}
		
		$this->ret_val['msg'][] = $this->_logText('Stack(s) dropped.');
		$this->status(1);
	}
	
	# check inventory ==========================================================
	function inventory()
	{
		$this->load->model('actor');
		$this->actor->setLast($this->who['actor']);
		$inv = $this->actor->getItems($this->who['actor']);
		$this->ret_val['enc'] =
			$this->actor->getEncumbrance($this->who['actor']);
		$this->ret_val['inv'] = $inv;
	}
	
	# use an item ==============================================================
	function item($item)
	{
		$this->actor->setLast($this->who['actor']);
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0)
			return;
		$this->load->model('item');
		$item = $this->item->getInfo($item);
		if($item === false) return $this->ret_val['msg'][] = "Error";
		$s = <<<SQL
			select 1 from actor_item ai
			join item_trigger it on ai.inum = it.inum
			where ai.instance = ? and it.use = b'1'
SQL;
		$q = $this->db->query($s, $item['instance']);
		
		if($q->num_rows() > 0)
		{
			$which = "i_{$item['abbrev']}";
			$this->load->model("items/{$which}");
			$num = func_num_args();
			
			if($num == 1)
				$ret = $this->$which->fire($item, $this->who, $this->who);
			else
			{
				# grab target actor's info to pass
				$args = array();
				$target = $this->actor->getInfo(func_get_arg(1));
				# grab other params
				for($a = 2; $a <= func_num_args; $args[] = func_get_arg($a++));
				$ret = $this->$which->fire($item, $this->who, $target, $args);
			}
			
			foreach($ret as $r)
				$this->ret_val['msg'][] = $this->_logText($r);
			$this->status(1);
		}
	}
	
	# assign ammo to a weapon ==================================================
	function loadweapon($weapon, $ammo)
	{
		$ret = $this->actor->loadWeapon($weapon, $ammo, $this->who['actor']);
		foreach($ret as $r)
			$this->ret_val['msg'][] = $this->_logText($r);
	}
	
	# get ammo options =========================================================
	function ammo($item)
	{
		$items = explode(',', $item);
		$this->load->model('item');
		
		foreach($items as $k => $i)
		{
			$instance = $this->actor->getInstanceOf($i, $this->who['actor']);
			
			if(! $instance)
			{
				$this->ret_val['msg'][] = $this->_logText(
					"You do not possess such a weapon.");
				return;
			}
			
			$classes = $this->item->getClasses($instance);			
			
			foreach($classes as $c)
				if($c['abbrev'] == 'bow')
				{
					$ammo =
						$this->actor->getItems($this->who['actor'], 'arrow');
					break;
				}
			
			$this->ret_val['ammo'][$k] = array();
			$this->ret_val['ammo'][$k]['instance'] = $instance;
			$this->ret_val['ammo'][$k]['opts'] = array();
			foreach($ammo as $a)
				$this->ret_val['ammo'][$k]['opts'][] =
					array('instance' => $a['instance'],
						'iname' => $a['iname'] . " [{$a['num']}]",
						'inum' => $a['inum']);
		}
	}
	
	# describe something =======================================================
	function describe($class, $key)
	{
		switch($class)
		{
			case 'actor':
			{
				$who = $this->actor->getInfo($key);
				$this->ret_val['aname'] = $who['aname'];
				$this->ret_val['descr'] = $this->actor->getEquipment($key);
				break;
			}
			
			case 'effect':
			{
				$this->load->model('effects');
				$this->ret_val['eff'] = $this->effects->getDescription($key);
				break;
			}
			
			case 'item':
			{
				$this->load->model('item');
				$desc = $this->item->describe($key, true);
				if(! $desc)
					return $this->ret_val['msg'][] = $this->_logText(
						"You do not possess such an item.");
				$ret = array('txt' => $desc['txt'], 'img' => $desc['img'],
					'iname' => $desc['iname'],
					'iclass' => $this->item->getClasses($key));
				
				# durability
				if(! is_null($desc['durmax']))
				{
					$ret['durability'] = $desc['durability'];
					$ret['durmax'] = $desc['durmax'];
				}
				
				# weapon
				if(! is_null($desc['dmg_min']))
				{
					$ret['weapon'] = array();
					$ret['eq_type'] =
						($desc['eq_type'] ? $desc['eq_type'] : 'N/A');
					$ret['weapon']['dmg_min'] = $desc['dmg_min'];
					$ret['weapon']['dmg_max'] = $desc['dmg_max'];
					$ret['weapon']['dmg_bonus'] = $desc['dmg_bonus'];
					$ret['weapon']['distance'] = $desc['distance'];
					$ret['weapon']['dmg_type'] = $desc['dmg_type'];
				}
				
				# armor
				if(! is_null($desc['aclass']))
				{
					$ret['armor'] = array();
					$ret['eq_type'] = $desc['eq_type'];
					$ret['armor']['class'] = $desc['aclass'];
					$ret['armor']['slashing'] = $desc['slashing'];
					$ret['armor']['piercing'] = $desc['piercing'];
					$ret['armor']['blunt'] = $desc['blunt'];
				}
				
				# ammo
				if($desc['dmg'] != null)
				{
					$ret['ammo'] = array();
					$ret['ammo']['dmg'] = $desc['dmg'];
				}
				
				$this->ret_val['descr'] = $ret;
				break;
			}
		}
	}
	
	# build menu for actions against an actor ==================================
	function actor($actor, $descr = false)
	{
		$this->actor->setLast($this->who['actor']);
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0)
			return;
		$who = $this->actor->getInfo($actor);
		if(! $who)
			return $this->ret_val['msg'][] =
				$this->_logText('Error retrieving actor.');
		
		if($actor > 0 && ($who['x'] != $this->who['x']
			|| $who['y'] != $this->who['y']
			|| $who['map'] != $this->who['map']
			|| $who['indoors'] != $this->who['indoors']
			|| ! $this->actor->isVisible($actor)))
		{
			return $this->ret_val['msg'][] =
				$this->_logText('They are not here.');
		}
		
		$this->ret_val['aname'] = $who['aname'];
		$this->ret_val['faction'] = $who['faction'];
		$this->ret_val['faction_name'] = $who['faction_name'];
		$this->ret_val['clan'] = $who['clan'];
		$this->ret_val['clan_name'] = $who['clan_name'];
		if($who['user'] <= 0)
			$this->ret_val['npc'] = 1;
		$this->load->model('clan');
		$rel = $this->clan->existsRelation($this->who['clan'], $who['clan']);
		
		if($rel)
		{
			$myclan = $this->clan->getInfo($this->who['clan']);
			$clan = $this->clan->getInfo($who['clan']);
			if($myclan['faction'] == $clan['faction'])
				$this->ret_val['rel'] = 'Ally';
			else
				$this->ret_val['rel'] = 'Enemy';
		}
		
		if($actor > 0)
		{
			$now = time();
			$diff = $now - $who['last'];
			$myelev = $this->actor->isElevated($this->who['actor']);
			$theirelev = $this->actor->isElevated($actor);
			if($myelev != $theirelev)
				$this->ret_val['dist'] = 1;
			else
				$this->ret_val['dist'] = 0;
		}

		$this->load->model('skills');
		$any = 0;
		
		if($this->skills->canMelee($actor, $this->who['actor'], 'melee'))
			$any = 1;
		else
		{
			$weps = $this->actor->getWeapons($actor['actor']);
			foreach($weps as $w) if($w['distance'] != 'melee') $any = 1;
		}
		
		$this->ret_val['attack'] = $any;
		# calculate chance to hit
		$this->ret_val['cth'] = round(
			$this->actor->getChanceToHit($this->who, $who) / 20 * 100);
		# get active skills
		$skills = $this->actor->getSkills($this->who, 2);
		
		foreach($skills as $sk)
		{
			$which = $sk['abbrev'];
			$this->load->model("skills/{$which}");
			
			if($this->$which->show($this->who, $who)
				!== false)
			{
				$this->ret_val['skills'][] = $sk;
			}
		}
		
		# get usable items
		if($actor > 0)
		{
			$items = $this->actor->getItems($this->who['actor']);
			$this->ret_val['items'] = array();
			foreach($items as $i)
				if($i['target'] > 0)
					$this->ret_val['items'][] = array('i' => $i['instance'],
						'n' => $i['iname']);
			$now = time();
			$diff = $now - $who['last'];
			switch(true)
			{
				case ($diff < 120):
				{
					$this->ret_val['status'] = 2;
					break;
				}
				default:
				{
					$this->ret_val['status'] = 1;
					break;
				}
			}
			
			$hp = floor($who['stat_hp'] / $who['stat_hpmax'] * 100);
			
			switch(true)
			{
				case ($hp >= 100):
				{
					$this->ret_val['health'] = 'Perfect';
					break;
				}
				case ($hp > 75):
				{
					$this->ret_val['health'] =
						'<span style="color:yellow">Fair</span>';
					break;
				}
				case ($hp > 50):
				{
					$this->ret_val['health'] =
						'<span style="color:yellow">Wounded</span>';
					break;
				}
				case ($hp > 25):
				{
					$this->ret_val['health'] =
						'<span style="color:red">Badly injured</span>';
					break;
				}
				case ($hp > 0):
				{
					$this->ret_val['health'] =
						'<span style="color:red">Bleeding profusely</span>';
					break;
				}
				default:
				{
					$this->ret_val['health'] =
						'<span style="color:black;">Dead</span>';
					break;
				}
			}
			
			if($descr !== false)
				$this->ret_val['descr'] =
					$this->actor->getEquipment($this->who['actor']);
		}
		
		$this->load->model('action');
		$aactions = $this->action->getActors($this->who, $actor);
		
		foreach($aactions as $k => $aa)
			if($aa['params'])
			{				
				$which = $aa['abbrev'];
				$this->load->model("actions/actor/{$which}");
				$aactions[$k]['params'] = $this->$which->params(
					$this->who, $retval, array($who));
			}
			
		if($aactions) $this->ret_val['acta'] = $aactions;
	}

	# attack an actor ==========================================================
	function attack($victim)
	{
		$this->actor->setLast($this->who['actor']);
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0)
			return;
		if($this->actor->isOverEncumbered($this->who['actor']))
			return $this->ret_val['msg'][] = $this->_logText(
				"You are too encumbered to attack.");
		$ret = $this->actor->attack($victim, $this->who);
		foreach($ret as $m) $this->ret_val['msg'][] = $this->_logText($m);
		$this->status(1);
	}
	
	# equip items ==============================================================
	function equip($items)
	{
		$this->load->model('actor');
		$this->actor->setLast($this->who['actor']);
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0)
			return;
		$items = split('-', $items);
		$msg = array();
		
		foreach($items as $i)
		{
			$ret = $this->actor->equipItems($i, $this->who);
			foreach($ret as $r)
				$this->ret_val['msg'][] = $this->_logText($r);
		}
		
		$this->status(1);
	}
	
	# remove items =============================================================
	function remove($items)
	{
		$this->actor->setLast($this->who['actor']);
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0)
			return;
		$items = split('-', $items);
		
		foreach($items as $i)
		{
			$ret = $this->actor->removeItems($i, $this->who);
			foreach($ret as $r)
				$this->ret_val['msg'][] = $this->_logText($r);
		}
		
		$this->status(1);
	}
	
	# use a skill ==============================================================
	function skill($skill)
	{
		$this->load->model('actor');
		$this->actor->setLast($this->who['actor']);
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0)
			return;
		if($this->actor->isOverEncumbered($this->who['actor']))
			return $this->ret_val['msg'][] = $this->_logText(
				"You are too encumbered to move.");
		# get parameters
		$c = func_num_args();
		$params = 0;
		
		if($c == 2)
		{
			if(is_array(func_get_arg(1)))		
				$params = func_get_arg(1);
			else
				$params = array(func_get_arg(1));
		} else {
			$params = array();
			for($a = 1; $a < $c; $params[] = func_get_arg($a++));
		}
		
		$ret = $this->actor->useSkill($skill, $this->who, $params);
		foreach($ret as $r)
			$this->ret_val['msg'][] = $this->_logText($r);
		if(! preg_match('#/repeat/#i', $_SERVER['REQUEST_URI']))
			$this->status(1);
	}
	
	# use an action ============================================================
	function action($type, $abbrev)
	{
		if($abbrev[0] == '_' || $this->who['stat_ap'] <= 0 ||
			($this->who['stat_hp'] <= 0 && $type != 'dead'))
		{
			return;
		}
		
		$c = func_num_args();
		$params = 0;
		
		if($c == 3)
		{
			if(is_array(func_get_arg(2)))
				$params = func_get_arg(2);
			else
				$params = array(func_get_arg(2));
		} else {
			$params = array();
			for($a = 2; $a < $c; $params[] = func_get_arg($a++));
		}
		
		$retval = array();
		$this->load->model("actions/{$type}/{$abbrev}");
		$res = $this->$abbrev->fire($this->who,$retval, $params);
		foreach($retval as $k => $v) $this->ret_val[$k] = $v;
		foreach($res as $r) $this->ret_val['msg'][] = $this->_logText($r);
		if(! preg_match('#/repeat/#i', $_SERVER['REQUEST_URI']))
			$this->status(1);
	}
	
	# repeat x5 ================================================================
	function repeat($what)
	{
		$c = func_num_args();
		
		if ($what == 'action')
		{
			$this->load->model('action');
			$type = func_get_arg(1);
			$act = func_get_arg(2);
			if(! $this->action->isRepeatable($type, $act))
				return false;
			$params = array();
			for($a = 3; $a < $c; $params[] = func_get_arg($a++));
			
			for($a = 0; $a < 5; $a++)
			{
				$this->action($type, $act, $params);
				$this->who = $this->actor->getInfo(
					$this->session->userdata('actor'));
			}
		}
		else if ($what == 'skill')
		{
			$this->load->model('skills');
			$skill = func_get_arg(1);
			if(! $this->skills->isRepeatable($skill)) die("not repeatable");
			$params = array();
			for($a = 2; $a < $c; $params[] = func_get_arg($a++));
			for($a = 0; $a < 5; $a++)
			{
				$this->skill($skill, $params);
				$this->who = $this->actor->getInfo(
					$this->session->userdata('actor'));
			}
		}
		
		$this->status(1);
	}
	
	# load the view for an action ==============================================
	function actionview($type, $abbrev)
	{
		if($abbrev[0] == '_' || $this->who['stat_ap'] <= 0 ||
			($this->who['stat_hp'] <= 0 && $type != 'dead'))
		{
			return;
		}
		
		$this->load->view("actions/{$type}/{$abbrev}");
	}
	
	# send chat text/commands ==================================================
	function chat()
	{
		if($this->who['stat_ap'] <= 0 || $this->who['stat_hp'] <= 0)
			return;
		if(strlen(trim($this->input->post('text'))) <= 0) return;
		preg_match('#^(/[a-z0-9]+ )(.+)$#i', $this->input->post('text'), $matches);
		
		# command?
		if($matches[1])
		{
			switch(strtolower(substr($matches[1], 1, strlen($matches[1]) - 2)))
			{
				case 'me':
				case 'emote':
				case 'e':
				{
					$txt = htmlentities(substr($matches[2], 0, 160));
					$this->map->sendCellEvent(
						"{$this->who['aname']} <i>{$txt}</i>", false,
						$this->who['map'], $this->who['x'], $this->who['y'],
						$this->who['indoors']);
					break;
				}
				case 'w':
				case 'whisper':
				case 'm':
				case 'msg':
				case 'message':
				{
					$txt = substr(trim($matches[2]), 0, 160);
					$sendto = '';
					$msg = '';
					
					if($txt[0] == '"')
					{
						# quotation-mark-enclosed name
						preg_match('#^"(.*?)"\s+?(.+)$#i', $txt, $sub);
						$sendto = $sub[1];
						$msg = $sub[2];
					}
					else
					{
						# "naked" name (no quotation marks)
						$sendto = substr($txt, 0, strpos($txt, ' '));
						if($sendto == "") $sendto = $txt;
						$msg = substr($txt, strlen($sendto));
					}
					
					# no whisper target
					if($sendto == "")
					{
						$this->ret_val['msg'][] =
							$this->_logText("No target given.");
						die();
					}
					
					if($msg == "")
					{
						$this->ret_val['msg'][] =
							$this->_logText("No message given.");
						die();
					}
					
					$this->load->database();
					$s = <<<SQL
						select aname, actor, stat_hp
						from actor where lower(aname) = ?
							and map = ? and x = ? and y = ? and indoors = ?
						and user > 0
SQL;
					$q = $this->db->query($s, array(
						strtolower($sendto), $this->who['map'], $this->who['x'],
							$this->who['y'], $this->who['indoors']));
					
					# target not found
					if($q->num_rows() <= 0)
					{
						$this->ret_val['msg'][] = $this->_logText(
							"Target not found.");
						die();
					}
					
					$r = $q->row_array();
					
					if($r['actor'] == $this->who['actor'])
					{
						$this->ret_val['msg'][] = $this->_logText(
							"You whisper to yourself. You feel pretty stupid.");
						die();
					}
					
					if($r['stat_hp'] <= 0)
					{
						$this->ret_val['msg'][] = $this->_logText(
							"You whisper, but the dead cannot hear you.");
						die();
					}
					
					$this->actor->sendEvent(
						"{$this->who['aname']} whispers, <i>\"{$msg}\"</i>",
						$r['actor']);
					$this->ret_val['msg'][] = $this->_logText(
						"You whisper to {$r['aname']}, <i>\"{$msg}\"</i>");
					break;
				}
				default:
				{
					return $this->ret_val['msg'][] =
						$this->_logText('Invalid command.');
					break;
				}
			}
		}
		else
		{
			$txt = htmlentities(substr($this->input->post('text'), 0, 160));
			$this->map->sendCellEvent(
				"{$this->who['aname']} says, <i>\"{$txt}\"</i>",
				array($this->who['actor']),
				$this->who['map'], $this->who['x'], $this->who['y'],
				$this->who['indoors']);
			$this->ret_val['msg'][] =
				$this->_logText("You say, <i>\"{$txt}\"</i>");
		}
		
		$ret = $this->actor->removeEffect('hiding', $this->who);
		foreach($ret as $r)
			$this->ret_val['msg'][] = $this->_logText($r);
		$this->status(1);
	}
	
	# get a skill's parameters =================================================
	function skillparams($skill)
	{
		$this->load->model('skills');
		$skillInfo = $this->skills->getInfo($skill);
		$this->ret_val['sname'] = $skillInfo['sname'];
		$this->ret_val['params'] =
			$this->skills->getParameters($skill, $this->who);
	}
	
	# get an action's parameters ===============================================
	function actparams($type, $action)
	{
		$this->load->model('action');
		$actInfo = $this->action->getInfo($type, $action);
		$this->ret_val['actname'] = $actInfo['descr'];
		$this->ret_val['params'] = 
			$this->action->getParameters($type, $action, $this->who['actor']);
	}
	
	# generate time-stamped log text ===========================================
	function _logText($txt, $stamp = false)
	{
		if($txt == '') return '';
		$t = time();
		if($stamp === false) $stamp = $t;		
		if(date('z', $stamp) != date('z', $t))
			$stamp = date('M d H:i:s', $stamp);
		else
			$stamp = date('H:i:s', $stamp);
		return array($stamp, $txt);
	}
}
