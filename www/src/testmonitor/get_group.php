<?php
require_once 'SOAP/Client.php';
include('../common/tc_functions.php');

$coreid = $_REQUEST['user_name'];

$xml = simplexml_load_string(GetAllGroupsByCoreid($coreid));

if(count($xml->Table) > 0){
	$result = array();
	$result[0]["id"] = 0;
	$result[0]["text"] = "--Select group--";

	$id_num = 1;
	foreach ($xml->Table as $tableList){
		$ele = array();
		$ele['id'] = trim($tableList->groupName);
		$ele['text'] = trim($tableList->groupName);
		
		array_push($result, $ele);
		$id_num++;
	}

	echo json_encode($result);
}

?>
