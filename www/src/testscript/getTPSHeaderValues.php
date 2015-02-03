<?php
require_once 'SOAP/Client.php';  //http://pear.php.net/package/SOAP

$testsuitename = $_POST['testsuitename'];

$jsonString = file_get_contents("/datafiles/logfiles/columns_by_suite.json");
$jsonArr = json_decode($jsonString, true);

foreach ($jsonArr as $key => $value) {
	if ($key == $testsuitename) {
		echo $value;
		break;
	}
}
?>