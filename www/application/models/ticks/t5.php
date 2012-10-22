<?php if(! defined('BASEPATH')) exit();

class t5 extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	function fire()
	{
		# handle inspirations
		$sql = <<<SQL
			delete from pdata where dtype = 'effect'
				and dkey like 'insp_%'
				and cast(dval as signed) < 0
SQL;
		$this->db->query($sql);
		$sql = <<<SQL
			update pdata set dval = cast(dval as signed) - 1
			where dtype = 'effect' and dkey like 'insp_%'
SQL;
		$this->db->query($sql);		
	}
}
