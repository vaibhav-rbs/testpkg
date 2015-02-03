<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/app_log.php');

$user_name = $_REQUEST['user_name'];
$index = $_REQUEST['index'];
$changes = $_REQUEST['changes'];
$temp_file = '../../tempdata/testexec/' . $user_name . '_testjob_temp.json';
$arr_content = array();
$arr_indexes = json_decode($index, true);
$arr_changes = json_decode($changes, true);

readTempJSON($temp_file);

// get rows from the content
$rows = $arr_content["rows"];

foreach($arr_changes as $key => $row) {
	$index = $arr_indexes[$key];
	$rows[$index] = $arr_changes[$key];
}

// update content
$arr_content["rows"] = $rows;

writeTempJSON($temp_file);

function readTempJSON($file) {
	global $arr_content;
	
	$fp = fopen($file, 'r');
	$content = fread($fp, filesize($file));
	fclose($fp);
	
	$arr_content = json_decode($content, true);
}

function writeTempJSON($file) {
	global $arr_content;
	
	$fp = fopen($file, 'w');
	fwrite($fp, json_encode($arr_content));
	fclose($fp);
}
?>