<?php
require(dirname(__FILE__) . '/config.php');
$payload = getPayload();
$url = $config['url'] . $payload->tick;
$stamp = time();
$nonce = $stamp . "|" . base64_encode(md5($config['salt'] . $stamp));
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, array("nonce" => $nonce));
echo curl_exec($ch);
curl_close($ch);

