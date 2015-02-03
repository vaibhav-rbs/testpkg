<?php
require_once 'SOAP/Client.php';
include('../common/tc_functions.php');

$coreid = $_REQUEST['user_name'];
//$coreid = "crmg76";

$xml = simplexml_load_string(GetAllGroupsByCoreid($coreid));




if(count($xml->Table) > 0){
	$result = array();
	$result[0]["id"] = 0;
	$result[0]["text"] = "--Select group--";
	$result[0]["selected"] = true;

	$id_num = 1;
	foreach ($xml->Table as $tableList){
		$ele = array();
		$ele['id'] = $id_num;
		$ele['text'] = trim($tableList->groupName);

		array_push($result, $ele);
		$id_num++;
	}

	echo json_encode($result);
}

?>
