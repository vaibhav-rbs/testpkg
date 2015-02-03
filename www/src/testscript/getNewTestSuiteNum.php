<?php
require_once 'SOAP/Client.php'; 
include('../common/tc_functions.php');
$funcArea = $_POST['funcArea'];
$groupName = $_POST['groupName'];
$xml = simplexml_load_string(Get_Test_Suite_By_Functional_Area($funcArea));
$max = 0;

if (count($xml->Table) > 0) {
	foreach($xml->Table as $table) {
		//array_push($result, trim($table->TestCaseName));
		$group = trim($table->GroupName);
		
		if ($group == $groupName) {
			$name = trim($table->TestSuiteName);
			$name = substr($name, strrpos($name, ":") + 1);
			$num = intval($name);
			
			if ($num > $max) {
				$max = $num;
			}
		}
	}
}

$result = $max + 1;
echo (string)$result;
?>