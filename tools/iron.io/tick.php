<?php
require(dirname(__FILE__) . '/config.php');
$payload = getPayload();
$url = $config['url'] . $payload->tick;
$success = false;

for($a = 0; $a < 5 && ! $success; $a++)
{
	$stamp = time();
	$nonce = $stamp . "|" . base64_encode(md5($config['salt'] . $stamp));
	$ch = curl_init($url);
	#curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, array("nonce" => $nonce));
	$result = curl_exec($ch);
	$success = (strpos($result, 'Tick fired.') !== false);

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

