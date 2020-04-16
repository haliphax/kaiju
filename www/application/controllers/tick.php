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
				die(show_404());
			else if(abs(time() - $nonce[0]) > 60)
				die(show_404());
			else
				define('CMD', 1);
		}

		# don't allow web access to this controller
		if(! defined('CMD'))
			die(show_404());
		$this->load->database();
	}

	function twitter()
	{
		$feed = $this->input->post('feed');
		$xml = simplexml_load_string($feed);
		$news = "<ul id='tweets'>";
		$tweets = 0;

		foreach($xml as $status)
		{
			$news .= "<li>";
			$text = $status->text;

			# convert URLs into links
			$text = preg_replace(
				"#(https?://([-a-z0-9]+\.)+[a-z]{2,5}([/?][-a-z0-9!\#()/?&+]*)?)#i", "<a href='$1' target='_blank'>$1</a>",
				$text);
			# convert protocol-less URLs into links
			$text = preg_replace(
				"#(?!https?://|<a[^>]+>)(^|\s)(([-a-z0-9]+\.)+[a-z]{2,5}([/?][-a-z0-9!\#()/?&+.]*)?)\b#i", "$1<a href='http://$2'>$2</a>",
				$text);
			# convert @mentions into follow links
			$text = preg_replace(
				"#(?!https?://|<a[^>]+>)(^|\s)(@([_a-z0-9\-]+))#i", "$1<a href=\"https://twitter.com/$3\" title=\"Follow $3\" target=\"_blank\">@$3</a>",
				$text);
			# convert #hashtags into tag search links
			$text = preg_replace(
				"#(?!https?://|<a[^>]+>)(^|\s)(\#([_a-z0-9\-]+))#i", "$1<a href=\"https://twitter.com/search?q=%23$3\" title=\"Search tag: $3\" target=\"_blank\">#$3</a>",
				$text);	

			$news .= "{$text}<span>";
			$news .= date('D M j @ g:i A', strtotime($status->created_at) + (-5 * 60));
			$news .= "</span></li>";

			if(++$tweets == 5)
				break;
		}

		$news .= "</ul>";
		$this->load->spark("cache/2.0.0");
		$this->cache->write($news, "twitter_feed", 905);
		echo "Cache set.\n";
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
				print_r($this->$which->tick());
			}
		}
		
		echo "Tick fired.\n";
	}
}
