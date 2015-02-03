<?php
require_once 'SOAP/Client.php';
include('../common/app_log.php');

$user_name = $_GET['user_name'];
$cycleName = $_POST['cycleplan'];

app_log_line("cycleName = " . $cycleName);

//$cycleName = "Kukak - Invader Demo (MD Advance Platforms) Cycle 1";
//$cycleName = "Kukak - IR14 BAT (MD Advance Platforms) Cycle 1";
//$cycleName = "demo_product - test job test plan (MD Advance Platforms) Cycle 1";

sleep(6); // Give test central some time to finish previous updating

$xml_default = simplexml_load_string(getTestCases_default($cycleName));
$xml = simplexml_load_string(getTestCases($cycleName));

//app_log_line("xml = " . getTestCases($cycleName));
//app_log_line("count xml_default = " . count($xml_default->Table));
//app_log_line("count xml = " . count($xml->Table));

$rows = array();
$target = array();
if(count($xml_default->Table) > 0){
	
	foreach($xml_default->Table as $table) {
	//for($index = $offset; $index < ($offset + $count_rows); $index++) {
		
		//$table = $xml_default->Table[$index];
		
		app_log_line("For " . trim((string)$table->children()->testCaseName));
		
		$row = array();
		$target = null;	
		//$row["testCaseName"] = trim((string)$table->children()->testCaseName);
		$case_name = trim((string)$table->children()->testCaseName);
		//$row["testCaseName"] = '<a href="#" onClick="popinfo(\'' . $case_name . '\')">' . $case_name . '</a>';
		$row["testCaseName"] = $case_name . ' <img src="themes/icons/help_12.png" onClick="showTestDetail2(\'' . $case_name . '\')">';
		$row["testResult"] = trim((string)$table->children()->testResult);
		$row["groupTypeValue1"] = trim((string)$table->children()->groupTypeValue1);
		if (count($xml->Table) > 0){
			foreach($xml->Table as $table2) {
				if (trim((string)$table2->children()->testCaseName) == $case_name){
					$target = $table2;
					//app_log_line("Search " . $row["testCaseName"]);
				}
			}
			if($target){
				$row["defectReportId"] = trim((string)$target->children()->defectReportId);
				$row["blockedReason"] = trim((string)$target->children()->blockedReason);
				$row["executionMethod"] = trim((string)$target->children()->executionMethod);
				$row["comments"] = trim((string)$target->children()->comments);
			}else{
				app_log_line("Target is null");
				$row["defectReportId"] = "";
				$row["blockedReason"] = "";
				$row["executionMethod"] = "";
				$row["comments"] = "";

			}
		}else{
			$row["defectReportId"] = "";
			$row["blockedReason"] = "";
			$row["executionMethod"] = "";
			$row["comments"] = "";

		}
		$row["lastUpdUser"] = trim((string)$table->children()->lastUpdUser);
		$row["lastUpdDate"] = trim((string)$table->children()->lastUpdDate);
		
		
		// simply update with latest test results history of the test case
		$name_key = $case_name . "::" . $row["groupTypeValue1"];
		$rows[$name_key] = $row;
	}
	sort($rows);
}

// save to temp json
$result['testplan'] = $cycleName;
$result['rows'] = $rows;
$file = '../../tempdata/testexec/' . $user_name . '_testexeclist.json';
$fp = fopen($file, 'w');
fwrite($fp, json_encode($result));
fclose($fp);

echo 1;
	

function getTestCases($planName){
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_executionService.asmx?WSDL';
	$executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestResultHistoryByTestPlanName($planName);
	
	return $executionHistory;
}



function getTestCases_default($planName){
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
        $executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
        $executionServiceClient = $executionServiceWsdl->getProxy();
        $executionServiceClient->setOpt('timeout', 200);
        $executionHistory = $executionServiceClient->Interface_GetTestCaseInfoByPlan($planName);
        //app_log_line("xml_default = " . $executionHistory);
        return $executionHistory;
}

?>
