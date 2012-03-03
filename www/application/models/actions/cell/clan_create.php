<?php if(! defined('BASEPATH')) exit();

class clan_create extends NoCacheModel
{
	private $ci;
	
	function clan_create()
	{
		parent::__construct();
		$this->ci =& get_instance();
	}
	
	function fire(&$actor, &$retval, $params)
	{
		if(! $this->show($actor)) return;
		$this->load->database();
		$clanname = trim($this->input->post('name'));
		if(! $clanname) return;
		if(preg_match("#[^- ':a-z0-9]+#i", $clanname))
			return array("Allowed characters are: space, -, ', :, a-z, 0-9");
		if(strlen($clanname) < 8)
			return array("Clan name must be at least 8 characters long.");
		if(strlen($clanname) > 32)
			return array("Clan name must not exceed 32 characters in length.");
		$q = $this->db->query(
			'select 1 from clan where lcase(descr) = lcase(?)', $clanname);
		if($q->num_rows() > 0)
			return array("A clan by that name already exists.");
		$policy = $this->input->post('policy');
		if(! $policy) return;
		$this->db->query(
			'insert into clan (descr, faction, policy) values (?, ?, ?)',
			array($clanname, $actor['faction'], $policy));
		$id = $this->db->insert_id();
		if(! is_int($id)) return array("Error inserting clan record.");
		$this->db->query(
			'insert into clan_actor (clan, actor, rank) values (?, ?, 0)',
			array($id, $actor['actor']));
		$this->ci->load->model('actor');
		$this->ci->actor->setStatFlag($actor['actor']);
		return array("Your clan, {$clanname}, has been created!");
	}
	
	function show(&$actor)
	{
		return (! $actor['clan'] && $actor['stat_xpspent'] >= 750);
	}
}