<?php if(! defined('BASEPATH')) exit();

class clans extends CI_Controller
{
	private $who;
	private $table_tmpl = array(
		'table_open'			=> '<table style="width:100%" cellspacing="0" cellpadding="4">',
		'table_close'			=> '</table>',
		'heading_row_start'		=> '<tr class="ui-state-focus" style="text-align:center">',
		'heading_row_end'		=> '</tr>',
		'row_alt_start'			=> '<tr class="ui-state-highlight">',
		'row_alt_end'			=> '</tr>',
		);

	function clans()
	{
		parent::__construct();
		$this->load->library('session');
		
		if(file_exists($this->config->item('maintfile'))
			|| $this->session->userdata('user') === false
			|| $this->session->userdata('actor') === false)
		{
			$this->output->set_header('Location: ' . site_url('login'));
			die();
		}
		
		$this->load->model('actor');
		$this->who = $this->actor->getInfo($this->session->userdata('actor'));
		$this->load->model('clan');
	}
	
	function index($forcelist = false)
	{
		$data = array();
		
		if($this->who['clan'] && ! $forcelist)
		{
			# character is in a clan
			$clan = $this->clan->getInfo($this->who['clan']);
			
			if($clan['x'])
			{
				$this->load->model('map');
				$clan['stronghold'] = $this->map->getCellInfo($clan['map'],
					$clan['x'], $clan['y']);
			}
			
			$this->load->model('faction');
			$faction = $this->faction->getInfo($clan['faction']);
			$clan['faction_name'] = $faction['descr'];
			$clan['roster'] = $this->clan->getRoster($this->who['clan']);
			$clan['successors'] = array();
			
			if($this->who['actor'] == $clan['leader'])
			{
				$clan['isleader'] = 1;
				$members = $this->clan->getRoster($this->who['clan']);
				foreach($members as $member)
					if($member['actor'] != $this->who['actor'])
						$clan['successors'][] = $member;
			}
			
			$this->load->view('clan', $clan);
		}
		else
		{
			$this->load->library('table');
			$this->table->set_heading('Clan', 'Message', '');
			$this->table->set_template($this->table_tmpl);
			$invites = false;
			
			if(! $this->who['clan'])
			{
				$invites = $this->actor->getInvitations($this->who['actor']);
				foreach($invites as $inv)
					$this->table->add_row(
						"<span style='display:none'>{$inv['clan']}</span><a href='#'>{$inv['descr']}</a>",
						$inv['msg'], '');
			}
			
			if($invites) $data['my'] = $invites;
			$data['myclan'] = $this->who['clan'];
			$this->load->view('clans', $data);
		}
	}

	function all()
	{
		$this->index(true);
	}
	
	function list_open()
	{
		$this->load->database();
		foreach($r as $row)
			$factions[$row['faction']] = $row['descr'];
		$s = <<<SQL
			select c.clan, c.faction, c.descr, f.descr as faction_descr,
				count(ca.actor) as members
			from clan c join faction f on c.faction = f.faction
			join clan_actor ca on c.clan = ca.clan
			where policy = 'open'
			group by c.clan
			order by lower(c.descr) asc
SQL;
		$q = $this->db->query($s);
		$r = $q->result_array();
		$this->load->library('table');
		$this->table->set_heading('Clan', 'Faction', 'Members', '');
		$this->table->set_template($this->table_tmpl);
		foreach($r as $row)
			$this->table->add_row(
				"<span style='display:none'>{$row['clan']}</span><a href='#'>{$row['descr']}</a>",
				"<a href='#'>{$row['faction_descr']}</a>",
				$row['members'],
				($row['faction'] == $this->who['faction']
					&& ! $this->who['clan']
					? "<button class='button'>Apply</button>"
					: "")
				);
		echo $this->table->generate();
	}

	function list_closed()
	{
		$this->load->database();
		foreach($r as $row)
			$factions[$row['faction']] = $row['descr'];
		$s = <<<SQL
			select c.clan, c.faction, c.descr, f.descr as faction_descr,
				count(ca.actor) as members
			from clan c join faction f on c.faction = f.faction
			join clan_actor ca on c.clan = ca.clan
			where policy = 'closed'
			group by c.clan
			order by lower(c.descr) asc
SQL;
		$q = $this->db->query($s);
		$r = $q->result_array();
		$this->load->library('table');
		$this->table->set_heading('Clan', 'Faction', 'Members');
		$this->table->set_template($this->table_tmpl);
		foreach($r as $row)
			$this->table->add_row(
				"<span style='display:none'>{$row['clan']}</span><a href='#'>{$row['descr']}</a>",
				"<a href='#'>{$row['faction_descr']}</a>",
				$row['members']
				);
		echo $this->table->generate();
	}
	
	function apply()
	{
		if($this->who['clan']) return;
		$msg = $this->input->post('msg');
		$this->load->model('clan');
		$which = $this->input->post('clan');
		$clan = $this->clan->getInfo($which);
		if($clan['faction'] != $this->who['faction']) return;
		if($clan['policy'] != 'open') return;
		if(! $this->clan->submitApplication($which, $this->who['actor'], $msg))
			return;
		echo json_encode(array('success' => 1));
	}
	
	function options()
	{
		$clan = $this->clan->getInfo($this->who['clan']);
		if($clan['leader'] != $this->who['actor']) return;
		$policy = $this->input->post('policy');
		if(! $policy) return;
		
		if(! $this->clan->setOptions($this->who['clan'], array(
			'policy' => $policy)))
		{
			return;
		}
		
		echo json_encode(array('success' => 1));
	}
	
	function list_applications()
	{
		$clan = $this->clan->getInfo($this->who['clan']);
		if($this->who['actor'] != $clan['leader']) return;
		$apps = $this->clan->getApplications($this->who['clan']);
		if(! $apps) die('<p>None.</p>');
		$this->load->library('table');
		$this->table->set_template($this->table_tmpl);
		$this->table->set_heading('Character', 'Message', '');
		foreach($apps as $app)
			$this->table->add_row(
				"<span style='display:none'>{$app['actor']}</span><a href='#'>{$app['aname']}</a>",
				$app['msg'],
				'');
		echo $this->table->generate();
	}
	
	function list_invitations()
	{
		$clan = $this->clan->getInfo($this->who['clan']);
		if($this->who['actor'] != $clan['leader']) return;
		$invs = $this->clan->getInvitations($this->who['clan']);
		if(! $invs) die('<p>None.</p>');
		$this->load->library('table');
		$this->table->set_template($this->table_tmpl);
		$this->table->set_heading('Character', 'Message', '');
		foreach($invs as $inv)
			$this->table->add_row(
				"<span style='display:none'>{$inv['actor']}</span><a href='#'>{$inv['aname']}</a>",
				$inv['msg'],
				'');
		echo $this->table->generate();	
	}
	
	function accept_application($actor)
	{
		$clan = $this->clan->getInfo($this->who['clan']);
		if($this->who['actor'] != $clan['leader']) return;		
		if(! $this->clan->acceptApplication($this->who['clan'], $actor))
			return;
		$who = $this->actor->getInfo($actor);
		$claninfo = $this->clan->getInfo($clan);
		$this->clan->sendEvent(
			"<b>{$who['aname']} has been accepted into {$claninfo['descr']}.</b>",
			$this->who['clan'], array($actor, $this->who['actor']));			
		echo json_encode(array('success' => 1));
	}
	
	function deny_application($actor)
	{
		$clan = $this->clan->getInfo($this->who['clan']);
		if($this->who['actor'] != $clan['leader']) return;
		if(! $this->clan->denyApplication($this->who['clan'], $actor))
			return;
		echo json_encode(array('success' => 1));
	}
	
	function cancel_invitation($actor)
	{
		$clan = $this->clan->getInfo($this->who['clan']);
		if($this->who['actor'] != $clan['leader']) return;		
		if(! $this->clan->cancelInvitation($this->who['clan'], $actor))
			return;
		echo json_encode(array('success' => 1));	
	}
	
	function send_invitation()
	{
		$actor = trim($this->input->post('actor'));
		$actor = $this->actor->getByName($actor);
		$msg = trim($this->input->post('msg'));
		$clan = $this->clan->getInfo($this->who['clan']);
		if($this->who['actor'] != $clan['leader']) return;
		$who = $this->actor->getInfo($actor);
		if($who['faction'] != $clan['faction']) return;
		if($who['clan']) return;
		if(! $this->clan->sendInvitation($this->who['clan'], $actor, $msg))
			return;
		echo json_encode(array('success' => 1));
	}
	
	function accept_invitation($clan)
	{
		if(! $this->actor->acceptInvitation($this->who['actor'], $clan))
			return;
		$claninfo = $this->clan->getInfo($clan);
		$this->clan->sendEvent(
			"<b>{$this->who['aname']} has accepted their invitation to join {$claninfo['descr']}.</b>",
			$clan, array($this->who['actor']));
		echo json_encode(array('success' => 1));
	}
	
	function deny_invitation($clan)
	{
		if(! $this->actor->denyInvitation($this->who['actor'], $clan))
			return;
		echo json_encode(array('success' => 1));
	}
	
	function list_roster()
	{
		$clan = $this->clan->getInfo($this->who['clan']);
		$roster = $this->clan->getRoster($this->who['clan']);
		$this->load->library('table');
		$this->table->set_template($this->table_tmpl);
		$this->table->set_heading('Character', '');
		foreach($roster as $r)
			$this->table->add_row(
				"<span style='display:none'>{$r['actor']}</span><a href='#'>{$r['aname']}</a>"
					. ($r['actor'] == $clan['leader']
						? ' <small><i>Leader</i></small>' : ''),
				'');
		echo $this->table->generate();
	}
	
	function remove_member($who)
	{
		$clan = $this->clan->getInfo($this->who['clan']);
		if($this->who['actor'] != $clan['leader']) return;
		$who = $this->actor->getInfo($who);
		if($who['clan'] != $this->who['clan']) return;
		if(! $this->clan->removeMember($this->who['clan'], $who['actor']))
			return;
		$this->clan->sendEvent(
			"<b>{$who['aname']} has been removed from the roster of {$clan['descr']}.</b>",
			$this->who['clan'], array($this->who['actor']));
		echo json_encode(array('success' => 1));
	}
	
	function leave()
	{
		if(! $this->who['clan'])
		{
			$this->output->set_header("Location: " . site_url("clans"));
			return;
		}
		
		$this->actor->leaveClan($this->who['actor']);
		$this->output->set_header("Location: " . site_url("clans"));
	}
	
	function list_relations()
	{
		if(! $this->who['clan']) return;
		$rels = $this->clan->getRelations($this->who['clan']);
		
		if(! $rels)
			echo '<p>None.</p>';
		else
		{
			foreach($rels as $rel)
				$relations[] = array(
					"<span style='display:none'>{$rel['rclan']}</span><a href='#'>{$rel['descr']}</a>",
					$rel['standing'],
					'');
			$this->load->library('table');
			$tmpl = $this->table_tmpl;
			$tmpl['table_open'] =
				'<table style="width:100%" cellspacing="0" cellpadding="4" id="relations_table">';
			$this->table->set_template($tmpl);
			$this->table->set_heading('Clan', 'Standing', '');
			echo $this->table->generate($relations);
		}
	}
	
	function remove_relation($which)
	{
		if(! $this->who['clan']) return;
		$clan = $this->clan->getInfo($this->who['clan']);
		if($this->who['actor'] != $clan['leader']) return;
		if(! $this->clan->existsRelation($this->who['clan'], $which)) return;
		if(! $this->clan->removeRelation($this->who['clan'], $which)) return;
		$rclan = $this->clan->getInfo($which);
		$this->clan->sendEvent(
			"<b>{$clan['descr']} withdrew interest in {$rclan['descr']}.</b>",
			$this->who['clan'], array($this->who['actor']));
		echo json_encode(array('success' => 1));
	}
	
	function add_relation($which)
	{
		if(! $this->who['clan']) return;
		$clan = $this->clan->getInfo($this->who['clan']);
		if($this->who['actor'] != $clan['leader']) return;
		if($this->clan->existsRelation($this->who['clan'], $which)) return;
		$rclan = $this->clan->getInfo($which);
		$standing = 'Ally';
		if($clan['faction'] != $rclan['faction']) $standing = 'Enemy';
		if(! $this->clan->addRelation($this->who['clan'], $which)) return;
		$this->clan->sendEvent(
			"<b>{$clan['descr']} claimed {$rclan['descr']} as an {$standing}.</b>",
			$this->who['clan'], array($this->who['actor']));
		echo json_encode(array('success' => 1));
	}
	
	function info($which)
	{
		$clan = $this->clan->getInfo($which);
		$myclan = $this->clan->getInfo($this->who['clan']);	
		$rel = $this->clan->existsRelation($this->who['clan'], $which);
		
		if($this->who['actor'] == $myclan['leader'] && ! $rel
			&& $this->who['clan'] != $which)
		{
			$clan['isleader'] = 1;
		}
		
		if($clan['faction'] == $myclan['faction']) $clan['fmatch'] = 1;
		$this->load->model('faction');
		$faction = $this->faction->getInfo($clan['faction']);
		$clan['faction_name'] = $faction['descr'];
		
		if($rel)
		{
			if($clan['fmatch'])
				$clan['rel'] = 'Ally';
			else
				$clan['rel'] = 'Enemy';
		}
		
		echo json_encode($clan);
	}
	
	function stepdown()
	{
		if(! $this->who['clan']) return;
		$clan = $this->clan->getInfo($this->who['clan']);
		if($clan['leader'] != $this->who['actor']) return;
		$successor = $this->input->post('successor');
		
		if($successor == 0)
		{
			$this->clan->disband($this->who['clan']);
			echo json_encode(array('success' => 1));
			return;
		}
		
		$who = $this->actor->getInfo($successor);
		if($who['clan'] != $this->who['clan']) return;
		if(! $this->clan->replaceLeader($this->who['clan'], $successor))
			return;
		$this->clan->sendEvent(
			"<b>{$this->who['aname']} has appointed {$who['aname']} as the new leader of {$clan['descr']}.</b>",
			$who['clan'], array($successor, $this->who['actor']));
		$this->actor->sendEvent(
			"<b>You have been appointed as the new leader of {$clan['descr']}.</b>",
			$successor);
		echo json_encode(array('success' => 1));		
	}
}
