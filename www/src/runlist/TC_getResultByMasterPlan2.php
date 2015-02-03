<?php

require_once 'SOAP/Client.php';

/*
echo "Do you want to get data by name of master plan? or by a sub-string that all the names of master plans contain?\n";
echo "	1. I want to input a master plan name\n";
echo "	2. I want to input a group name and a sub-string for searching\n";
echo "	(Please type 1 or 2)\n";
$STDIN = fopen("php://stdin","r");
$line = fgets($STDIN);
$flag = chop($line);
fclose($STDIN);

if($flag == "1") {
	echo "Please type name of master plan:\n";
	$MNAME = fopen("php://stdin","r");
	$line = fgets($MNAME);
	$masterplanname = chop($line);
	getdata1($masterplanname);
}
*/

echo "\n===================================================================\n";
echo "TestCentral Test results extractor\n";
echo "===================================================================\n\n";
echo "Please type name of group:\n";
$GNAME = fopen("php://stdin","r");
$line = fgets($GNAME);
$groupname = chop($line);
echo "Please type the string for searching [type * for all]:\n";
$STR = fopen("php://stdin","r");
$line = fgets($STR);
$str = chop($line);
//getdata2($groupname, $str);
getTestResults($groupname, $str);
pushToBucket();

function getdata1($MasterPlan){

	$master_out = get_cycle_data_by_master_plan($MasterPlan);
	//echo $master_out;
	if(file_exists("/tmp/test_central.json")){
		$cmd = "sudo rm /tmp/test_central.json";
		exec($cmd);
	}
	$fhandle = fopen("/tmp/test_central.json","w");
	fwrite($fhandle,$master_out);
	fclose($fhandle);
	/*
	echo "****************************************************************\n";
	echo "Please type 'cat /tmp/test_central.json' to see the result data!\n";
	echo "****************************************************************\n";
	*/
	
	pushToBucket();
}

function getTestResults($group, $search) {
	// open file for write
	$file = fopen("/tmp/test_central.json", "w");
	
	$list = array();
	$xmlMasterPlans = simplexml_load_string(Get_Test_Plans("Master Plan", $group));
	
	foreach($xmlMasterPlans->Table as $table) {
		$masterPlanName = $table->testplanname;
		
		if (strpos($masterPlanName, $search) !== false || $search == "*") {
			array_push($list, $masterPlanName);
		}
	}
	
	// start processing master plans
	$totalMaster = count($list);
	
	foreach($list as $key => $item) {
		$index = $key + 1;
		$percent = round(($index/$totalMaster) * 100);
		echo "[$index/$totalMaster] Processing $item...[$percent%]\n";
		
		// get cycle plans
		$xmlCyclePlans = simplexml_load_string(getCyclePlans($item));
		$totalCycle = $xmlCyclePlans->Table->count();
		
		$index = 0;
		foreach($xmlCyclePlans->Table as $table) {
			$index++;
			$completePercent = round(($index/$totalCycle) * 100);
			
			$cycle = $table->testplanname;
			echo "\r   > ($index/$totalCycle) Processing $cycle...[$completePercent%]";
			
			// get test results
			$xmlTestResults = simplexml_load_string(getTestResultHistory($cycle));
			$totalTestResult = $xmlTestResults->Table->count();
			
			foreach($xmlTestResults->Table as $table) {
				// write to json file
				$arrTestResult = array(
					'test_case_name' => (string)$table->testCaseName->{0},
					'run_date' => (string)$table->runDate->{0},
					'test_plan_name' => (string)$table->testPlanName->{0},
					'group_type_value1' => (string)$table->groupTypeValue1->{0},
					'group_type_value2' => (string)$table->groupTypeValue2->{0},
					'test_result' => (string)$table->testResult->{0},
					'execution_method' => (string)$table->executionMethod->{0},
					'tester' => (string)$table->tester->{0},
					'test_upd_user' => (string)$table->lastUpdUser->{0},
					'last_upd_date' => (string)$table->lastUpdDate->{0},
					'test_result_id' => (string)$table->testResultID->{0},
					'comments' => (string)$table->comments->{0},
					'defect_report_id' => (string)$table->defectReportId->{0},
					'blocked_reason' => (string)$table->blockedReason->{0}
				);
				
				fwrite($file, json_encode($arrTestResult) . PHP_EOL);
			}
		}
		
		echo "\n";
	}
	
	// close the file
	fclose($file);
}

/*
function getdata2($groupName,$MasterPlanString){

	$MasterPlanList = Get_Test_Plans("Master Plan",$groupName);
	$xml_ret2 = simplexml_load_string($MasterPlanList);

	//print_r($xml_ret2);
	$selected_list = array();
	$num2 = count($xml_ret2->Table);
	for ($nn=0 ; $nn < $num2 ; $nn++){
		$masterplanname = $xml_ret2->Table[$nn]->testplanname;
		if (strpos($masterplanname,$MasterPlanString) !== false || $MasterPlanString == '*') {
    			array_push($selected_list,$masterplanname);	
		}
	}

	$num4 = count($selected_list);
	$all_out = "";
	for ($ii = 0; $ii < $num4 ; $ii++){
		$index = $ii + 1;
		echo "[$index/$num4] Processing $selected_list[$ii]...\n";
		
		if($ii!=0) $all_out = $all_out . "\n";
		$all_out = $all_out . get_cycle_data_by_master_plan($selected_list[$ii]);
	}

	if(file_exists("/tmp/test_central.json")){
		$cmd2 = "sudo rm /tmp/test_central.json";
		exec($cmd2);
	}
	$fhandle2 = fopen("/tmp/test_central.json","w");
	fwrite($fhandle2,$all_out);
	fclose($fhandle2);

	pushToBucket();
}*/

function pushToBucket() {
	echo "\nPushing test_central.json to Google Storage bucket...\n";
	shell_exec('gsutil mv /tmp/test_central.json gs://test_central/test_central.json');
	echo "----------------------------------------------------------------\n";
	echo "Done!\n";
	echo "----------------------------------------------------------------\n";
}

/*
function get_cycle_data_by_master_plan($MasterPlan){
	//$MasterPlan = "xFone JB MASTER - 3rdPartyFunctionalSanity (MD Android Sys_Test)";

	$ArrayOne = array();
	$ArrayNew = array();
	$out = "";

	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$result = $executionServiceClient->Interface_GetTestPlans("Cycle Plan","Testing","and tp.parentDetail = '" . $MasterPlan . "' order by Cycle asc");

	$xml_ret = simplexml_load_string($result);
	$num = count($xml_ret->Table)-1;

	for ($i=0 ; $i<$num+1 ; $i++){
        	$testPlan = $xml_ret->Table[$i]->testplanname;
        	$testPlan = chop($testPlan);

        	$index = $i + 1;
        	$total = $num + 1;
        	echo "\r   [$index/$total] Processing $testPlan...";

        	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ExecutionService.asmx?WSDL';
        	$executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
        	$executionServiceClient = $executionServiceWsdl->getProxy();
        	$executionServiceClient->setOpt('timeout', 200);
        	$executionReturn = $executionServiceClient->Interface_GetTestResultHistoryByTestPlanName($testPlan);


        	$result_ret = simplexml_load_string($executionReturn);
        	$num_cases = count($result_ret->Table);
        	for ($j=0 ; $j<$num_cases ; $j++){
                	array_push($ArrayOne, $result_ret->Table->{$j});
        	}
	}
	
	echo "\n";


	/*
	$str = $ArrayOne[0]->testCaseName->{0};
	echo $str . "\n";
	exit(0);
	

	$num3 = count($ArrayOne);


	for ($j=0 ; $j<$num3 ; $j++){
		
		//if($j!=0) $out = $out . "\n"; -- no new lines
		$ArrayNew[$j] = array();
		$ArrayNew[$j]["test_case_name"] = (string)$ArrayOne[$j]->testCaseName->{0};
		$ArrayNew[$j]["run_date"] = (string)$ArrayOne[$j]->runDate->{0};
		$ArrayNew[$j]["test_plan_name"] = (string)$ArrayOne[$j]->testPlanName->{0};
		$ArrayNew[$j]["group_type_value1"] = (string)$ArrayOne[$j]->groupTypeValue1->{0};
		$ArrayNew[$j]["group_type_value2"] = (string)$ArrayOne[$j]->groupTypeValue2->{0};
		$ArrayNew[$j]["test_result"] = (string)$ArrayOne[$j]->testResult->{0};
		$ArrayNew[$j]["execution_method"] = (string)$ArrayOne[$j]->executionMethod->{0};
		$ArrayNew[$j]["tester"] = (string)$ArrayOne[$j]->tester->{0};
		$ArrayNew[$j]["test_upd_user"] = (string)$ArrayOne[$j]->lastUpdUser->{0};
		$ArrayNew[$j]["last_upd_date"] = (string)$ArrayOne[$j]->lastUpdDate->{0};
		$ArrayNew[$j]["test_result_id"] = (string)$ArrayOne[$j]->testResultID->{0};
		$ArrayNew[$j]["comments"] = (string)$ArrayOne[$j]->comments->{0};
		$ArrayNew[$j]["defect_report_id"] = (string)$ArrayOne[$j]->defectReportId->{0};
		$ArrayNew[$j]["blocked_reason"] = (string)$ArrayOne[$j]->blockedReason->{0};
	
		/*
		if ($ArrayOne[$j]->comments != null) $ArrayNew[$j]["comments"] = $ArrayOne[$j]->comments;
		if ($ArrayOne[$j]->defectReportId != null) $ArrayNew[$j]["defectReportId"] = $ArrayOne[$j]->defectReportId;
		if ($ArrayOne[$j]->blockedReason != null) $ArrayNew[$j]["blockedReason"] = $ArrayOne[$j]->blockedReason;

		
		$out = $out . json_encode($ArrayNew[$j]);

	}

	//print_r($ArrayNew);
	return $out;
}*/

function Get_Test_Plans($plan,$groupname) {
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
    $executionServiceClient   = $executionServiceWsdl->getProxy();
    $executionServiceClient->setOpt('timeout', 200);
    $executionHistory = $executionServiceClient->Interface_GetTestPlans($plan,"Testing","and tp.groupId in(select groupId from groups where groupName = '".$groupname."')");

    return $executionHistory;
}

function getCyclePlans($masterPlan) {
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$result = $executionServiceClient->Interface_GetTestPlans("Cycle Plan","Testing","and tp.parentDetail = '" . $masterPlan . "' order by Cycle asc");
	
	return $result;
}

function getTestResultHistory($cyclePlan) {
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ExecutionService.asmx?WSDL';
    $executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
    $executionServiceClient = $executionServiceWsdl->getProxy();
    $executionServiceClient->setOpt('timeout', 200);
    $executionReturn = $executionServiceClient->Interface_GetTestResultHistoryByTestPlanName(chop($cyclePlan));
    
    return $executionReturn;
}

?>

