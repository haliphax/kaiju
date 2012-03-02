<?php if ! defined('BASEPATH') exit();

interface NPC
{
	function spawn();
	function tick($tick);
	function defend(&$victim, &$actor, &$swing);
}