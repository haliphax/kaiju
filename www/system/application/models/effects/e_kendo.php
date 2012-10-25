<?php if(! defined('BASEPATH')) exit();

class e_kendo extends Model
{
	private $ci;
	
	function e_kendo()
	{
		parent::Model();
		$this->ci =& get_instance();
		$this->load->database();
	}

	function attack(&$victim, &$actor, &$swing)
	{
		$s = <<<SQL
			select 1 from actor_item ai
			join item_weapon iw on ai.inum = iw.inum
			join item i on ai.inum = i.inum
			where actor = ? and eq_slot is not null and distance != 'ranged'
				and eq_type = '2H'
SQL;
		$q = $this->db->query($s, $actor['actor']);
		if($q->num_rows() > 0) $swing['crit'] += 2;
	}
}