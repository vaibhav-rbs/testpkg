<?php
require_once 'SOAP/Client.php'; 
include('../common/tc_functions.php');
$suiteName = $_GET['suiteName'];
$xml = simplexml_load_string(Get_Test_Case_By_Suite($suiteName));
$max = 0;

if (count($xml->Table) > 0) {
	foreach($xml->Table as $table) {
		//array_push($result, trim($table->TestCaseName));
		$name = trim($table->TestCaseName);
		$name = substr($name, strrpos($name, "-") + 1);
		$num = intval($name);
		
		if ($num > $max) {
			$max = $num;
		}
	}
}

$result = $max + 1;
echo (string)$result;