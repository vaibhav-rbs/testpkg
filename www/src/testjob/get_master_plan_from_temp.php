<?php
require_once 'SOAP/Client.php';

$username = $_REQUEST['username'];

// get group name from the temp json file
$file = '../../tempdata/testexec/' . $username . '_testjob_temp.json';
$fp = fopen($file, 'r');

if ($fp) {
	$json = fread($fp, filesize($file));
	fclose($fp);
	
	// conver to array
	$array = json_decode($json, true);
	echo $array['testplan'];
}

