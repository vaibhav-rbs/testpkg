<?php
require_once 'SOAP/Client.php';
include('../common/tc_functions.php');

$coreid = $_REQUEST['user_name'];

$xml = simplexml_load_string(GetAllGroupsByCoreid($coreid));

// get group name from the temp json file
$file = '../../tempdata/testexec/' . $coreid . '_testjob_temp.json';
$fp = fopen($file, 'r');

if ($fp) {
	$json = fread($fp, filesize($file));
	fclose($fp);
	
	// conver to array
	$array = json_decode($json, true);
	$group = $array["group"];
}


if(count($xml->Table) > 0){
	$result = array();
	$result[0]["id"] = 0;
	$result[0]["text"] = "--Select group--";

	$id_num = 1;
	foreach ($xml->Table as $tableList){
		$ele = array();
		$ele['id'] = trim($tableList->groupName);
		$ele['text'] = trim($tableList->groupName);
		
		if($ele['text'] == $group) {
			$ele['selected'] = true;
		}

		array_push($result, $ele);
		$id_num++;
	}

	echo json_encode($result);
}

?>
