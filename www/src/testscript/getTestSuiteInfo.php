<?php
/*
 * getTestSuiteInfo
 * Jung Soo Kim
 * July 3, 2013
 */

require_once 'SOAP/Client.php';
include('../common/tc_functions.php');
$result = array();
$suite = $_GET['suite'];
//$suite = "Android Features.33749:001";
$xml = simplexml_load_string(get_test_suite_general_info($suite));

foreach ($xml as $table) {
	$tableName = $table->getName();
	$result[$tableName] = array();
	
	foreach ($table->children() as $child) {
		$item = array();
		$name = trim((string)$child->getName());
		
		switch ($name) {
			case 'TPSHeaderValue':
				$headers = array();
				foreach ($child->XMLDATA->COLUMNS->Column as $column) {
					array_push($headers, $column['name']);
				}
				$item[$name] = implode(', ', $headers);
				break;
			default:
				$item[$name] = trim($child);
				break;
		}
		
		array_push($result[$tableName], $item);
	}
}

echo json_encode($result);