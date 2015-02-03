<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/app_log.php');
include('update_testcase_functions.php');

/*
$test_or_suite = "newsuite.test:026-002";
$test_desc = "0716 modified test desciption";
$execution_method = "Manual";

//$jstring is the input string that we want to update test case
$jstring = file_get_contents("/var/www/TC/About_TestSuites/About_testcase/jungsoo.json");
*/


$test_name = $_POST['test_name'];
$test_desc = $_POST['test_desc'];
$jstring = $_POST['test_content'];
$execution_method = $_POST['exec_mode'];
$script_path = $_POST['script_path'];
$git_url = $_POST['git_url'];

/*app_log_line("Test Name = " . $test_name);
app_log_line("Test Desc = " . $test_desc);
app_log_line("Test Content = " . $jstring);
app_log_line("Exec Method = " . $execution_method);
app_log_line("Script Path = " . $script_path);*/

// save to database
//$test_name = "newsuite.test:072-002";
//$script_path = "tests/common-baseline/UI-automation/aPython_test/src/Browser/Test001.py:testcase005";

$query_result = add_script_path($test_name, $script_path, $git_url);

if ($query_result == 1) {
	save_test_case($test_name, $jstring, $test_desc, $execution_method, $script_path,1);
} else {
	echo $query_result;
}


?>
