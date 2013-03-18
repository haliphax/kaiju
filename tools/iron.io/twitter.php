<?php
require(dirname(__FILE__) . '/config.php');
$url = $config['url'] . 'twitter';
$success = false;
$feed = file_get_contents($config['twitter_url']);

for($a = 0; $a < 5 && ! $success; $a++)
{
	$stamp = time();
	$nonce = $stamp . "|" . base64_encode(md5($config['salt'] . $stamp));
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, array("nonce" => $nonce, "feed" => $feed));
	$result = curl_exec($ch);
	$success = (strpos($result, 'Cache set.') !== false);

	if($success)
	{
		echo curl_exec($ch);
	}
	else
	{
		echo "cURL Failure\n";
	}

	curl_close($ch);
}

