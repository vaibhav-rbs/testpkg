<?php
require_once 'SOAP/Client.php';
include('../common/app_log.php');
include('../db/api.php');

$user_name = $_GET['username'];
$plan_name = $_GET['planname'];
$group = $_REQUEST['group'];

$xml_default = simplexml_load_string(getTestCases_master($plan_name));
$testJobFile = file_get_contents('/datafiles/testjob/' . $_GET['filename']);
$arrTestJobFile = json_decode($testJobFile);

// get the test cases array in test job
$arrTestCasesInTestJob = array();
foreach ($arrTestJobFile->testcases as $arrTestCase) {
	array_push($arrTestCasesInTestJob, $arrTestCase->testCaseName);
}

$rows = array();
$target = array();

if(count($xml_default->Table) > 0){
	
	$count = 0;
	foreach($xml_default->Table as $table) {
		$row = array();
		
		$testcase_name = trim((string)$table->children()->testCaseName);
		$description = trim((string)$table->children()->caseDescription);
		$script_path = "";
		$git_url = "";
		$run = "";
		$set = "";
		$execMethod = "Manual";
		
		// get script path
		$arrScriptPath = get_script_path($testcase_name);
		
		// if script path exists, it is automated
		if (strlen($arrScriptPath[0]['script_path']) > 0) {
			$execMethod = "Automated";
		}
					
		// if test case exists in the test job, check run
		if (in_array($testcase_name, $arrTestCasesInTestJob)) {
			$run = "<img src='themes/icons/checkmark_12.png'>";
			$set = "1";	
		} else if ($execMethod == "Automated") {
			$run = "<img src='themes/icons/checkmark_12_lightgray.png'>";
		}

		$row["run"] = $run;
		$row["set"] = $set;
		$row["testCaseName"] = $testcase_name;
		$row["caseDescription"] = $description;
		$row["executionMethod"] = $execMethod;
		$row["scriptPath"] = $arrScriptPath[0]['script_path'];
		$row["gitPath"] = $arrScriptPath[0]['git_url'];
		
		array_push($rows, $row);
	}
	
	# sort result array by test case name
	function cmp($a, $b) {
		return strcmp($a['testCaseName'], $b['testCaseName']);
	}
	
	usort($rows, "cmp");
}

// save to temp json
$result['group'] = $group;
$result['testplan'] = $plan_name;
$result['rows'] = $rows;
$file = '../../tempdata/testexec/' . $user_name . '_testjob_temp.json';
$fp = fopen($file, 'w');
fwrite($fp, json_encode($result));
fclose($fp);

// echo the content of test job file to populate configurations
echo $testJobFile;

function getTestCases_master($planName){
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
    $executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
    $executionServiceClient = $executionServiceWsdl->getProxy();
    $executionServiceClient->setOpt('timeout', 200);
    $executionHistory = $executionServiceClient->Interface_GetTestCaseInfoByPlan($planName);
    //app_log_line("xml_default = " . $executionHistory);
    return $executionHistory;
}

?>
