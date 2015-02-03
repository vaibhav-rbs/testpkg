<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/app_log.php');

$user_name = $_REQUEST['user_name'];
$index = intval($_REQUEST['index']);
$direction = $_REQUEST['direction'];
$temp_file = '../../tempdata/testexec/' . $user_name . '_testjob_temp.json';
$arr_content = array();

readTempJSON($temp_file);

// get rows from the content
$rows = $arr_content["rows"];

switch($direction) {
	case "moveFirst":
		if ($index > 0) {
			$slice = array_slice($rows, $index, 1);
			$slice_head = array_slice($rows, 0, $index);
			$slice_tail = array_slice($rows, $index + 1);
			
			// arrange rows
			$rows = array_merge($slice, $slice_head, $slice_tail);
			
			// update content
			$arr_content["rows"] = $rows;
			
			$newIndex = 0; // reserve the new index row
		}
		break;
	case "moveLast":
		if ($index < count($rows)) {
			$slice = array_slice($rows, $index, 1);
			$slice_head = array_slice($rows, 0, $index);
			$slice_tail = array_slice($rows, $index + 1);
			
			// arrange rows
			$rows = array_merge($slice_head, $slice_tail, $slice);
			
			// update content
			$arr_content["rows"] = $rows;
			
			$newIndex = count($rows) - 1; // reserve the new index row
		}
		break;
	case "movePrev":
		if ($index > 0) {
			$temp = $rows[$index];
			
			// arrange rows
			$rows[$index] = $rows[$index - 1];
			$rows[$index - 1] = $temp;
			
			// update content
			$arr_content["rows"] = $rows;
			
			$newIndex = $index - 1; // reserve the new index row
		} else {
			$newIndex = $index;
		}
		break;
	case "moveNext":
		if ($index + 1 < count($rows)) {
			$temp = $rows[$index];
			
			// arrange rows
			$rows[$index] = $rows[$index + 1];
			$rows[$index + 1] = $temp;
			
			// update content
			$arr_content["rows"] = $rows;
			
			echo $index + 1; // reserve the new index row
		} else {
			$newIndex = $index;
		}
		break;
}

// save temp json file
writeTempJSON($temp_file);

// return new index
echo $newIndex;

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