<?php
/*
 * getTestCaseDetail
 * Jung Soo Kim
 * June 20, 2013
 */

require_once 'SOAP/Client.php';
include('../common/tc_functions.php');

$tcName = $_GET['tc'];
$xml = simplexml_load_string(Get_Test_CaseDetails_By_TestCase($tcName));

if (count($xml->Table) > 0) {
	$result = array();
	
	foreach ($xml->Table as $table) {
		$result['TestCaseName'] = trim($table->TestCaseName);
		$result['CaseDescription'] = trim($table->CaseDescription);
		$result['LastUpdDate'] = trim($table->LastUpdDate);
		
		//echo count($xml->Table->TestCaseTPSData->XMLDATA->COLUMNS->Column);
		if (count($table->TestCaseTPSData->XMLDATA->COLUMNS->Column) > 0) {
			$columns = array();
			
			foreach ($table->TestCaseTPSData->XMLDATA->COLUMNS->Column as $col) {
				//echo $col . "<br>";
				$column['column'] = $col;
				
				array_push($columns, $column);
			}
			
			$result['table'] = $columns;
		}
	}
}
echo var_dump($result).'<br><br>';
echo json_encode($result);
?>