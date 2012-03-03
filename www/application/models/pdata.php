<?php if(! defined('BASEPATH')) exit();

# persistent data ==============================================================

class pdata extends NoCacheModel
{
	function pdata()
	{
		parent::__construct();
		$this->load->database();
	}
	
	# retrieve pdata ===========================================================
	function get($dtype, $dkey, $owner = 0, $akey = 0)
	{
		$sql = <<<SQL
			select dval from pdata where
				dtype = ? and owner = ? and dkey = ? and altkey = ?
			limit 1
SQL;
		$query = $this->db->query($sql, array(
			$dtype, $owner, $dkey, $akey));
		if($query->num_rows() <= 0) return false;
		$res = $query->row_array();
		return $res['dval'];
	}
	
	# set pdata ================================================================
	function set($dtype, $dkey, $dval, $owner = 0, $akey = 0)
	{
		$this->db->trans_start();
		{
			$this->clear($dtype, $dkey, $owner, $akey);
			$sql = <<<SQL
				insert into pdata (dtype, owner, dkey, altkey, dval)
					values (?, ?, ?, ?, ?)
SQL;
			$this->db->query($sql, array(
				$dtype, $owner, $dkey, $akey, $dval));
		}
		$this->db->trans_complete();
	}
	
	# clear pdata ==============================================================
	function clear($dtype, $dkey, $owner = 0, $akey = 0)
	{
		$sql = <<<SQL
			delete from pdata where
				dtype = ? and owner = ? and dkey = ? and altkey = ?
SQL;
		$this->db->query($sql, array(
			$dtype, $owner, $dkey, $akey));	
	}
	
	# increment pdata ==========================================================
	function inc($dtype, $dkey, $inc, $owner = 0, $akey = 0)
	{
		$s = <<<SQL
			update pdata
			set dval = cast(dval as signed) + ?
			where dtype = ? and owner = ? and dkey = ? and altkey = ?
SQL;
		$this->db->query($s, array($inc, $dtype, $owner, $dkey, $akey));
	}
}