
<?php if(! defined('BASEPATH')) exit();

class item extends CI_Model
{
	private $ci;
	
	function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->ci =& get_instance();
		$this->ci->load->model('actor');
	}
	
	# describe an item =========================================================
	function describe($item, $instance = false)
	{
		$sql = '';
		
		if($instance)
		{
			$sql = <<<SQL
				select *, ai.durability as durability, durmax
					from actor_item ai
				join item i on ai.inum = i.inum
				left join item_ammo ia on i.inum = ia.inum
				left join item_armor ir on i.inum = ir.inum
				left join item_weapon iw on i.inum = iw.inum
				where ai.instance = ?
				limit 1
SQL;
		}
		else
		{
			$sql = <<<SQL
				select * from item i
				left join item_ammo ia on i.inum = ia.inum
				left join item_armor ir on i.inum = ir.inum
				left join item_weapon iw on i.inum = iw.inum
				where i.inum = ?
				limit 1
SQL;
		}
		
		$query = $this->db->query($sql, array($item));
		return $query->row_array();
	}
	
	# decrease durability ======================================================
	function decDurability($instance, &$actor)
	{
		$sql = <<<SQL
			select ai.durability, iname from actor_item ai
			join item i on ai.inum = i.inum
			where instance = ? and ai.durability is not null
			limit 1
SQL;
		$q = $this->db->query($sql, array($instance));
		if($q->num_rows() <= 0) return;
		$r = $q->row_array();
		$sql = <<<SQL
			update actor_item set durability = durability - 1
			where instance = ?
SQL;
		$this->db->query($sql, array($instance));
		
		# item broke
		if($r['durability'] <= 1)
		{
			$rret = $this->ci->actor->removeItems($instance, $actor);
			$ret[] = "Your {$r['iname']} was damaged beyond use.";
			foreach($rret as $rr) $ret[] = $rr;
			$sql = 'delete from actor_item where instance = ? and durmax = 0';
			$this->db->query($sql, array($instance));
			if($this->db->affected_rows() > 0)
				$ret[] = "Your {$r['iname']} was destroyed.";
		}
		else
			$ret = array("Your {$r['iname']} was damaged.");
		
		return $ret;
	}
	
	# get number of given item name ============================================
	function getByName($name)
	{
		$sql = 'select inum from item where lower(iname) = lower(?)';
		$query = $this->db->query($sql, array($name));
		$res = $query->row_array();
		return $res['inum'];
	}
	
	# get item info ============================================================
	function getInfo($item)
	{
		$sql = <<<SQL
			select iname, abbrev, i.inum, target, instance, ai.durability,
				durmax, ai.lifespan,
				(case when stack = b'1' then 1 else 0 end) as stack
			from actor_item ai
			join item i on ai.inum = i.inum
			where ai.instance = ?
SQL;
		$query = $this->db->query($sql, array($item));
		if($query->num_rows() <= 0) return false;
		return $query->row_array();
	}
	
	# get base item info =======================================================
	function getBaseInfo($item)
	{
		$sql = <<<SQL
			select iname, abbrev, inum, target, durability, lifespan,
				(case when repair = b'1' then 1 else 0 end) as repair
				from item
			where inum = ?
SQL;
		$query = $this->db->query($sql, array($item));
		if($query->num_rows() <= 0) return false;
		return $query->row_array();	
	}
	
	# get ammo info ============================================================
	function getAmmoDmg($item)
	{
		$sql = 'select dmg from item_ammo where inum = ?';
		$query = $this->db->query($sql, array($item));
		$res = $query->row_array();
		return $res['dmg'];
	}	
	
	# get item classes =========================================================
	function getClasses($item)
	{
		$sql = <<<SQL
			select distinct abbrev, descr from item_class ic
			join class_item ci on ic.iclass = ci.iclass
			join actor_item ai on ic.inum = ai.inum
			where instance = ?
SQL;
		$query = $this->db->query($sql, array($item));
		return $query->result_array();
	}
	
	# item has class? ==========================================================
	function hasClass($instance, $class)
	{
		$sql = <<<SQL
			select 1 item_class ic
			join class_item ci on ic.iclass = ci.iclass
			join actor_item ai on ic.inum = ai.inum
			where instance = ? and ci.abbrev = ?
SQL;
		$q = $this->db->query($sql, array($instance, $class));
		if($q->num_rows() > 0) return true;
		return false;
	}
}