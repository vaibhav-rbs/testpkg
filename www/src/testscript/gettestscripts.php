 
<?php
//Created by Snigdha Sivadas
//To get the suites for the functional area

require_once 'SOAP/Client.php';
//http://pear.php.net/package/SOAP
include ('../common/tc_functions.php');
$coreid = $_GET['coreid'];
$groupname = $_GET['groupname'];
$test_suite_list="";
$results1 = Get_Test_CaseSuite_By_GroupName($coreid);
$count_result=0;
$xml = simplexml_load_string($results1);
$input = array();
if (count($xml->Table) > 0) {
	foreach ($xml->Table as $node) {
		if($node->groupName == $groupname)
		array_push($input,$node->testSuiteName);
	}
	$result = array_unique($input);
}

	$test_suite_list = "[";
	foreach ($result as $output) {
			$test_suite_list = $test_suite_list . '{id:"1",text:"' . $output . '"';
   			$test_suite_list = $test_suite_list . '},';
	}

	if(strlen($test_suite_list)>1)
		$test_suite_list = substr($test_suite_list, 0, strlen($test_suite_list) - 1);
	$test_suite_list = $test_suite_list . "]";

$test_suite_list = $test_suite_list . '';
echo $test_suite_list;

?>
