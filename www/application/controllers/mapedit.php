<?php if(! defined('BASEPATH')) exit();

# map editor ===================================================================

class mapedit extends CI_Controller
{
	private $minisize = 3;
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('session');		
		if(! $this->session->userdata('user'))
			die(header('Location: ' . site_url('login')));
		$this->load->model('user');
		if(! $this->user->isMod($this->session->userdata('user')))
			die(header('Location: ' . site_url('game')));
		$this->load->model('map');
		$this->load->model('mapeditor');
	}
	
	# list maps ================================================================
	function index()
	{
		$this->load->view('mapedit/maplist', array(
			'maps' => $this->mapeditor->getMapList()));
	}
	
	# edit a map ===============================================================
	function edit($map)
	{
		if(! $this->user->canEditMap($this->session->userdata('user'), $map))
			die(header('Location: ' . site_url('mapedit')));
		$this->session->set_userdata('mapedit', $map);
		$map = $this->map->getInfo($map);
		$t = $this->mapeditor->getTiles();
		$tiles = array(array('img' => '', 'descr' => 'Empty'));
		foreach($t as $tile)
			$tiles[$tile['tile']] = array(
				'img' => $tile['img'],
				'descr' => $tile['descr']
				);
		$this->load->view('mapedit/mapedit',
			array(
				'map'		=> $map,
				'tiles'		=> $tiles,
				'size'		=> $this->minisize,
				'bclasses'	=> $this->map->getAllClasses()
				)
			);
	}
	
	# show minimap with bounding box ===========================================
	function mini($x, $y)
	{
		$map = $this->session->userdata('mapedit');
		if(! $this->user->canEditMap($this->session->userdata('user'), $map))
			die(header('Location: ' . site_url('mapedit')));
		$this->map->minisize = $this->minisize;
		$im = $this->map->getGif($map);
		$id = new ImagickDraw();
		$id->setFillOpacity(0.0);
		$id->setStrokeColor(new ImagickPixel('red'));
		$id->rectangle(($y - 1) * $this->minisize, ($x - 1) * $this->minisize,
			($y + 9) * $this->minisize - 1, ($x + 9) * $this->minisize - 1);
		$im->drawImage($id);
		$this->output->set_header('Content-Type: image/gif');
		echo $im;
	}
	
	# AJAX - chunk of map ======================================================
	function chunk($x, $y)
	{
		$map = $this->session->userdata('mapedit');
		if(! $this->user->canEditMap($this->session->userdata('user'), $map))
			die(header('Location: ' . site_url('mapedit')));
		echo json_encode($this->mapeditor->getChunk($map, $x, $y, 10));
	}
	
	# AJAX - building settings =================================================
	function get_building($x, $y)
	{
		$map = $this->session->userdata('mapedit');
		if(! $this->user->canEditMap($this->session->userdata('user'), $map))
			die(header('Location: ' . site_url('mapedit')));
		$cell = $this->map->getCellInfo($map, $x, $y);
		if(! $cell['building'])
			die(json_encode(array('error' => 1)));
		echo json_encode(array('classes' => $this->map->getBuildingClasses($map, $cell['building'])));
	}
	
	# AJAX - delete a building class ===========================================
	function remove_class($x, $y, $bclass)
	{
		$map = $this->session->userdata('mapedit');
		if(! $this->user->canEditMap($this->session->userdata('user'), $map))
			die(header('Location: ' . site_url('mapedit')));
		$cell = $this->map->getCellInfo($map, $x, $y);
		return $this->map->removeBuildingClass($map, $cell['building'], $bclass);
	}
	
	# AJAX - add a building class ==============================================
	
	function add_class($x, $y, $bclass)
	{
		$map = $this->session->userdata('mapedit');
		if(! $this->user->canEditMap($this->session->userdata('user'), $map))
			die(header('Location: ' . site_url('mapedit')));
		$cell = $this->map->getCellInfo($map, $x, $y);
		return $this->map->addBuildingClass($map, $cell['building'], $bclass);	
	}
	
	# AJAX - modify cells ======================================================
	function modcells()
	{
		$map = $this->session->userdata('mapedit');
		if(! $this->user->canEditMap($this->session->userdata('user'), $map))
			die(header('Location: ' . site_url('mapedit')));
		$cells = split(',', $this->input->post('cells'));
	
		foreach($cells as $c)
		{
			$cell = split('_', $c);
			if($cell[0] < 1 || $cell[1] < 1) continue;
			$sql = 
				'delete from map_cell where map = ? and x = ? and y = ?';
			$this->db->query($sql, array($map, $cell[0], $cell[1]));
			if($cell[2] == 0) continue;
			
			if($cell[3] == 0)
			{
				$sql = <<<SQL
					insert into map_cell (tile, map, x, y)
						values (?, ?, ?, ?)
SQL;
				$this->db->query($sql, array($cell[2], $map, $cell[0],
					$cell[1]));
			}
			else
			{
				$sql = <<<SQL
					insert into map_cell (tile, building, map, x, y)
						values (?, ?, ?, ?, ?)
SQL;
				$this->db->query($sql, array($cell[2], $cell[3], $map, $cell[0],
					$cell[1]));
			}
			
			if($cell[4])
			{
				$sql = <<<SQL
					update map_cell set descr = ?
					where map = ? and x = ? and y = ?
SQL;
				$this->db->query($sql, array($cell[4], $map, $cell[0],
					$cell[1]));
			}
		}
	}		
}
