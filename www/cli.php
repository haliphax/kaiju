#!/usr/bin/php
<?php
set_time_limit(0);
ini_set('memory_limit', '256M');
if (isset($_SERVER['REMOTE_ADDR'])) die('Permission denied.');
define('CMD', 1);
unset($argv[0]);
$_SERVER['QUERY_STRING'] = $_SERVER['PATH_INFO'] = $_SERVER['REQUEST_URI'] = '/' . implode('/', $argv) . '/';
require(dirname(__FILE__) . '/index.php');
