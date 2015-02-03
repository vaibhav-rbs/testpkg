<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/app_log.php');

$userName = $_REQUEST['userName'];
$index = $_REQUEST['index'];
$changes = $_REQUEST['changes'];
$temp_file = '../../tempdata/testexec/' . $userName . '_testjob_temp.json';

$arr_content = array();
$arr_changes = json_decode($changes, true);

readTempJSON($temp_file);

// get rows from the content
$rows = $arr_content["rows"];

// if index is NaN or not specified, it means update all rows
if ($index == NaN) {
	foreach($rows as $index => $row) {
		if ($row['executionMethod'] == 'Automated') {
			foreach($arr_changes as $key => $value) {
				$row[$key] = $value;
			}
			$rows[$index] = $row;	
		}
	}
} else {
	foreach($arr_changes as $key => $value) {
		$rows[$index][$key] = $value;
	}	
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