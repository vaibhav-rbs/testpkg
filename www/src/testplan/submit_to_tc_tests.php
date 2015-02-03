<?php

require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP

$corid=$_REQUEST['username'];
$plan=$_REQUEST['plan'];
$all_tests=$_REQUEST['all_tests'];

/*
$corid="crmg76";
$plan="Master : 3G K3 MASTER - Jing Test (MD Advance Platforms)";
$all_tests="{\"total\":0,\"rows\":[]}";
*/
/*
$corid="crmg76";
$plan="Cycle : 3G K3 MASTER - Jing Test2 (MD Android Sys_Test)";
$all_tests="{\"total\":5,\"rows\":[{\"name\":\"3rdPartyApps.AQuA:001-001\",\"description\":\"1.1 OTA install\"},{\"name\":\"3rdPartyApps.AQuA:001-002\",\"description\":\"1.2 Long Launch Time\"},{\"name\":\"3rdPartyApps.AQuA:001-003\",\"description\":\"1.3 Move to external memory (SD card)\"},{\"name\":\"3rdPartyApps.AQuA:001-004\",\"description\":\"1.4 Uninstall App\"},{\"name\":\"3rdPartyApps.AQuA:002-001\",\"description\":\"2.1 Memory during run\"}]}";
*/

list($plantype, $planname) = split(" : ",$plan);
list($junk1, $right) = split(" \(", $planname);
list($group, $junk2) = split("\)", $right);


$json_a = json_decode($all_tests, true);
if ($json_a["total"] == 0 && $plantype == "Cycle"){
	echo json_encode(array('msg'=>'No test cases for this cycle plan, fail to export!'));

}else{
	$num_tests = count($json_a["rows"]);
	$row_array = $json_a["rows"];
	$testcases = array();
	for($j = 0 ; $j < $num_tests ; $j++){
		$testcases[$j]["name"] = $row_array[$j]["name"];
	}

	$local_plan_file = "/datafiles/plans_creation_tmp/" . $corid;
	if(file_exists($local_plan_file)){
		$localString = file_get_contents($local_plan_file);
		$local_a = json_decode($localString, true);
		$a_count = count($local_a);
		$flag = 0;
		for($i = 0; $i < $a_count ; $i++){
			if ($local_a[$i]["planname"] == $planname && $local_a[$i]["plantype"] == $plantype){
				$flag = 1;
				$target = $i;
				break;
			}
		}
		if($flag == 1){
			$start = $local_a[$target]["start"] . " 12:00:00 AM";
			$end = $local_a[$target]["end"] . " 12:00:00 AM";
			$submit_array = array();
			$submit_array["corid"] = $corid;
			$submit_array["planname"] = $planname;
			$submit_array["plantype"] = $plantype;
			$submit_array["groupname"] = $group;
			$submit_array["testcases"] = $testcases;
			$submit_array["start"] = $start; 
			$submit_array["end"] = $end;

			$submit_str = json_encode($submit_array);

			$retvalue = (string)submit_to_tc($submit_str);

			if (strpos($retvalue,'has been created successfully') !== false) {
    				remove_plan($planname, $plantype,$corid);
			}



			echo json_encode(array('msg'=>$retvalue));


		}

	}



}

function remove_plan($planname, $plantype,$corid){
	$new_a = array();
	$local_plan_file = "/datafiles/plans_creation_tmp/" . $corid;
	if(file_exists($local_plan_file)){
		$string = file_get_contents($local_plan_file);
		$local_a = json_decode($string, true);
		$a_count = count($local_a);
		for($i = 0; $i < $a_count ; $i++){
			if ($local_a[$i]["planname"] == $planname && $local_a[$i]["plantype"] == $plantype){
			}else{
				array_push($new_a,$local_a[$i]);
			}
		}

		$fp = fopen($local_plan_file, 'w');
		fwrite($fp, json_encode($new_a));
		fclose($fp);

	}
	return;


}




function submit_to_tc($dataString){

	$input_array = json_decode($dataString, true);

	$plan_g_info = create_plan_g_info($input_array);
	$case_info = create_case_info($input_array);
	$case_info = str_replace("&", "&amp;", $case_info); 
	$corid = $input_array["corid"];
	$gname = $input_array["groupname"];
	$plan_d_info = "<NewDataSet/>";

	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$retStr = $executionServiceClient->Interface_ImportTestPlan($plan_g_info, $case_info, $plan_d_info, $corid, $gname);
	$xml = simplexml_load_string($retStr);

	return $xml->Table->ErrorDescription;
}

function create_plan_g_info($infoArray){

	$corid = $infoArray["corid"];
	$plantype = $infoArray["plantype"];
	$planname = $infoArray["planname"];
	$groupname = $infoArray["groupname"];
	$start = $infoArray["start"];
	$end = $infoArray["end"];


	$retString = "<?xml version=\"1.0\"?><NewDataSet><GeneralInformation><PlanType>" . $plantype . " Plan</PlanType>";

	if ($plantype == "Master"){
		list($product, $left) = split(" MASTER - ", $planname);
		list($scope, $left2) = split(" \(" , $left);
		list($group, $left3) = split("\)", $left2);

		$retString = $retString . "<MasterPlanProduct>" . $product . "</MasterPlanProduct>";
		$retString = $retString . "<MasterPlanScope>" . $scope . "</MasterPlanScope>";
		$retString = $retString . "<MasterPlanGroup>" . $group . "</MasterPlanGroup>";
	}
	$retString = $retString . "<TestPlanName>" . $planname . "</TestPlanName>";
	$retString = $retString . "<TCGroup1>1</TCGroup1><TCGroup2>1</TCGroup2>";
	$retString = $retString . "<OwnershipGroupName>" . $groupname . "</OwnershipGroupName>";
	$retString = $retString . "<ReadPermission>Motorola Only</ReadPermission><WritePermission>Group Only</WritePermission><PlanStatus>Testing</PlanStatus>";
	$retString = $retString . "<PlannedStartDate>" . $start . "</PlannedStartDate>";
	$retString = $retString . "<PlannedEndDate>" . $end . "</PlannedEndDate>";
	if ($plantype == "Cycle") $retString = $retString . "<CyclePlanType>Regresion</CyclePlanType>";
	$retString = $retString . "</GeneralInformation></NewDataSet>";
	return $retString;
}

function create_case_info($infoArray){
	$testcases = $infoArray["testcases"];
	$t_count = count($testcases);
	$retString = "<?xml version=\"1.0\"?><NewDataSet>";
	for ($i = 0; $i < $t_count ; $i++){
		$retString = $retString . "<CasesInformation><GroupTypeValue1>General</GroupTypeValue1><GroupTypeValue2>General</GroupTypeValue2><RegressionLevel>1</RegressionLevel><TestCaseName>" . $testcases[$i]["name"] . "</TestCaseName></CasesInformation>";
	}
	$retString = $retString . "</NewDataSet>";
	return $retString;
}



?>
