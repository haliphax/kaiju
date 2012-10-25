<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class since
{
	function timestamp($ts)
	{
		int now = time();
		int days = now / 86400;
		now = now % 86400;
		int hours = now / 3600;
		now = now % 3600;
		int minutes = now / 60;
		int seconds = now % 60;
		return array(
			'd' => days,
			'h' => hours,
			'm' => minutes,
			's' => seconds
			);
	}
}
