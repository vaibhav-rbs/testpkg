<?php
require_once 'SOAP/Client.php';
include('../common/app_log.php');


$user_name = $_GET['user_name'];


// read from temp json
$file = '../../tempdata/testexec/' . $user_name . '_testexeclist.json';

if (file_exists($file)) {
	$fp = fopen($file, 'r');
	$json = fread($fp, filesize($file));
	fclose($fp);
	
	$array = json_decode($json, true);
	
	echo $array["testplan"];	
}

?>