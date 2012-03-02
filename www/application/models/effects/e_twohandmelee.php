<?php if(! defined('BASEPATH')) exit();

class e_twohandmelee extends CI_Model
{
	private $ci;
	
	function e_twohandmelee()
	{
		parent::__construct();
		$this->ci =& get_instance();
		$this->load->database();
	}
	
	function chancetohit(&$actor)
	{
		$sql = <<<SQL
			select 1 from actor_item ai
			join item_weapon iw on ai.inum = iw.inum
			join item i on ai.inum = i.inum
			where actor = ? and eq_slot is not null and distance != 'ranged'
				and eq_type = '2H'
SQL;
		$query = $this->db->query($sql, array($actor['actor']));
		if($query->num_rows() > 0) return 5;
		return 0;
	}
}