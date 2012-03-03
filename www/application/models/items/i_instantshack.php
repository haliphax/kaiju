<?php if(! defined('BASEPATH')) exit();

class i_instantshack extends NoCacheModel
{
	private $ci;
	
	function i_instantshack()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
	}
	
	function fire(&$item, &$actor)
	{
		$this->ci->load->model('map');
		$this->ci->load->model('actor');
		$s = 'select building from map_cell where map = ? and x = ? and y = ?';
		$q = $this->db->query($s, array($actor['map'], $actor['x'],
			$actor['y']));
		$r = $q->row_array();
		if($r['building'])
			return array("There is already a building here.");
		$cell = $this->ci->map->getCellInfo($actor['map'], $actor['x'],
			$actor['y']);
		if($cell['tile'] > 2)
			return array("This land is not suitable for construction.");
		$s = "select structure from structure where descr = 'Shack'";
		$q = $this->db->query($s);
		$r = $q->row_array();
		$struct = $r['structure'];
		$s = <<<SQL
			insert into building (map, building, structure, owner, hp)
			values (?, ?, ?, ?, 120)
SQL;
		$y = str_pad($actor['y'], 3, '0', STR_PAD_LEFT);
		$bldg = $actor['x'] . $y;
		$this->db->query($s, array($actor['map'], $bldg, $struct,
			$actor['actor']));
		if($this->db->affected_rows() <= 0) return array("Error");
		$this->db->query(
			'update map_cell set tile = 26 where map = ? and x = ? and y = ?',
			array($actor['map'], $actor['x'], $actor['y']));
		if($this->db->affected_rows() <= 0) return array("Error");
		$s = <<<SQL
			update map_cell set building = ? where map = ? and x = ? and y = ?
SQL;
		$this->db->query($s, array($bldg, $actor['map'], $actor['x'],
			$actor['y']));
		if($this->db->affected_rows() <= 0) return array("Error");
		$this->ci->actor->dropItems(array($item['instance']), $actor['actor']);
		$this->ci->actor->setMapFlag($actor['actor']);
		return array(
			"You set down the suitcase, press a button on its handle, and BAM! Your Shack awaits."
			);	
	}
}