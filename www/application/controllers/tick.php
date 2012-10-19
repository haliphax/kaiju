<?php if(! defined('BASEPATH')) exit();

class tick extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$nonce = $this->input->post('nonce');
		
		if($nonce != "")
		{
			$nonce = explode("|", $nonce);
			if($nonce[1] != base64_encode(md5($this->config->item('salt') . $nonce[0])))
				die("Bad nonce");//die(show_404());
			else
				define('CMD', 1);
		}

		# don't allow web access to this controller
		if(! defined('CMD'))
			die(show_404());
		$this->load->database();
	}

	# ticks ====================================================================
	function fire($tick = 20)
	{
		$this->load->model('effects');
		$this->load->model('actor');

		$which = "t{$tick}";
		$this->load->model("ticks/{$which}");
		$this->$which->fire();

		$sql = <<<SQL
			select abbrev, tick from effect_trigger et
			join effect e on et.effect = e.effect
			where tick = ?
SQL;
		$query = $this->db->query($sql, array($tick));
		
		if($query->num_rows() > 0) {
			$res = $query->result_array();
			
			foreach($res as $r)
			{
				$which = "e_{$r['abbrev']}";
				$this->load->model("effects/{$which}");
				print_r(call_user_func(array($this->$which, "tick")));
			}
		}
		
		echo "Tick fired.\n";
	}
}
