<?php if(! defined('BASEPATH')) exit();

class Characters extends CI_Controller
{
	# constructor
	function Characters()
	{
		parent::__construct();
		
		if(file_exists($this->config->item('maintfile')))
		{
			header('Location: ' . site_url('login'));
			die();
		}
		
		$this->load->library('session');
		$this->load->model('user');
		$this->load->model('actor');
	}

	# default
	function index()
	{
		if($this->session->userdata('user') === false)
		{
			header('Location: ' . site_url('login'));
			return;
		}

		$chars = $this->user->getActors($this->session->userdata('user'));
		$data = array();

		if($chars)
		{
			$data['characters'] = $chars;
			
			foreach($data['characters'] as $k => $char)
			{
				$data['characters'][$k]['classes'] = '';
				$classes = 
					$this->actor->getClasses($data['characters'][$k]['actor']);
				foreach($classes as $kk => $class)
					$data['characters'][$k]['classes'] .=
						($kk > 0 ? ', ' : '') . $class['descr'];

				if($data['characters'][$k]['clan'])
				{
					$this->load->model('clan');
					$clan = $this->clan->getInfo(
						$data['characters'][$k]['clan']);
					$data['characters'][$k]['clan_name'] = $clan['descr'];
				}
			}			
		}
		else
			$data['characters'] = array(array('actor' => -1,
				'aname' => 'None'));
		
		$user = $this->user->getInfo($this->session->userdata('user'));
		
		if($user['slots'] >
			$this->user->getNumActors($this->session->userdata('user')))
		{
			$data['create'] = 1;
		}
-		
		$data['cur'] = $this->session->userdata('actor');
		$this->load->view('characters', $data);
	}

	# log in as character
	function connect($actor)
	{
		if($this->actor->getUser($actor) != $this->session->userdata('user'))
		{
			header('Location: ' . site_url('characters'));
			return;
		}
		
		$this->session->set_userdata('actor', $actor);
		header('Location: ' . site_url('preloader'));
	}
	
	# create a new character
	function create()
	{
		$user = $this->user->getInfo($this->session->userdata('user'));
		
		if($user['slots'] <=
			$this->user->getNumActors($this->session->userdata('user')))
		{
			header('Location: ' . site_url('characters'));
			return;
		}
		
		$data = array();
		$this->load->model('faction');
		$data['factions'] = $this->faction->getFactions();
		
		if($this->input->server('REQUEST_METHOD') == 'POST')
		{
			$name = $this->input->post('name');
			$len = strlen($name);
			
			if($len > 24)
			{
				$data['err'] = "Names may not exceed 24 characters in length.";
			}
			else if($len < 6)
			{
				$data['err'] = "Names must be at least 6 characters in length.";
			}
			else if(preg_match("#[^- 'a-z0-9]#i", $name) == 0)
			{
				$faction = $this->input->post('faction');
				$this->load->database();
				$sql = 'select 1 from actor where lower(aname) = ? limit 1';
				$query = $this->db->query($sql, array(strtolower($name)));
				
				if($query->num_rows() == 0)
				{
					$sql = 'select 1 from faction where faction = ?';
					$query = $this->db->query($sql, $faction);
					
					if($query->num_rows() == 0)
						$data['err'] = "Please choose a faction.";
					else
					{
						$sql = <<<SQL
							insert into actor (user, aname, faction)
							values (?, ?, ?)
SQL;
						$this->db->query($sql, array(
							$this->session->userdata('user'), $name, $faction));
						
						if($this->db->affected_rows() <= 0)
							$data['err'] = "Error creating character.";
						else
						{
							$actor = $this->db->insert_id();
							$sql = <<<SQL
								insert into actor_class (actor, aclass)
								values (?, 1)
SQL;
							$this->db->query($sql, array($actor));
							header("Location: " . site_url("characters/connect/$actor"));
							return;
						}
					}
				}
				else
					$data['err'] = "That character name is already taken.";
			}
			else
				$data['err'] =
					"Only alphanumerics, dashes (-), apostrophes ('), and spaces are allowed.";
		}
		
		$data['who'] = $this->who;
		$this->load->view('char_create', $data);
	}
}
