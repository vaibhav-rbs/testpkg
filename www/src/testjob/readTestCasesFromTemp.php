<?php
require_once 'SOAP/Client.php';
include('../common/app_log.php');


$user_name = $_GET['user_name'];

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 50;
$offset = ($page - 1) * $rows;

$portion = array();

// read from temp json
//$user_name = 'fbxj76';
$file = '../../tempdata/testexec/' . $user_name . '_testjob_temp.json';
$fp = fopen($file, 'r');

if ($fp) {
	$json = fread($fp, filesize($file));
	fclose($fp);
	
	// conver to array
	$array = json_decode($json, true);
	$testplan = $array["testplan"];
	$data = $array["rows"];
	$portion = array_slice($data, $offset, $rows);
}
	
$result["total"] = count($data);
$result["rows"] = $portion;

echo json_encode($result);
?>