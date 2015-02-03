<?php

require_once 'SOAP/Client.php';
$MasterPlan = "xFone JB MASTER - 3rdPartyFunctionalSanity (MD Android Sys_Test)";

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


        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ExecutionService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
        $executionServiceClient   = $executionServiceWsdl->getProxy();
        $executionServiceClient->setOpt('timeout', 200);
        $executionReturn = $executionServiceClient->Interface_GetTestResultHistoryByTestPlanName($testPlan);


        $result_ret = simplexml_load_string($executionReturn);
        $num_cases = count($result_ret->Table);
        for ($j=0 ; $j<$num_cases ; $j++){
                array_push($ArrayOne, $result_ret->Table->{$j});
        }
}


/*
$str = $ArrayOne[0]->testCaseName->{0};
echo $str . "\n";
exit(0);
*/

$num2 = count($ArrayOne);


for ($j=0 ; $j<$num2 ; $j++){

	if($j!=0) $out = $out . "\n";
	$ArrayNew[$j] = array();
	$ArrayNew[$j]["testCaseName"] = (string)$ArrayOne[$j]->testCaseName->{0};
	$ArrayNew[$j]["runDate"] = (string)$ArrayOne[$j]->runDate->{0};
	$ArrayNew[$j]["testPlanName"] = (string)$ArrayOne[$j]->testPlanName->{0};
	$ArrayNew[$j]["groupTypeValue1"] = (string)$ArrayOne[$j]->groupTypeValue1->{0};
	$ArrayNew[$j]["groupTypeValue2"] = (string)$ArrayOne[$j]->groupTypeValue2->{0};
	$ArrayNew[$j]["testResult"] = (string)$ArrayOne[$j]->testResult->{0};
	$ArrayNew[$j]["executionMethod"] = (string)$ArrayOne[$j]->executionMethod->{0};
	$ArrayNew[$j]["tester"] = (string)$ArrayOne[$j]->tester->{0};
	$ArrayNew[$j]["testUpdUser"] = (string)$ArrayOne[$j]->lastUpdUser->{0};
	$ArrayNew[$j]["lastUpdDate"] = (string)$ArrayOne[$j]->lastUpdDate->{0};
	$ArrayNew[$j]["testResultID"] = (string)$ArrayOne[$j]->testResultID->{0};
	$ArrayNew[$j]["lastUpdDate"] = (string)$ArrayOne[$j]->lastUpdDate->{0};
	$ArrayNew[$j]["comments"] = (string)$ArrayOne[$j]->comments->{0};
	$ArrayNew[$j]["defectReportId"] = (string)$ArrayOne[$j]->defectReportId->{0};
	$ArrayNew[$j]["blockedReason"] = (string)$ArrayOne[$j]->blockedReason->{0};
	
/*
	if ($ArrayOne[$j]->comments != null) $ArrayNew[$j]["comments"] = $ArrayOne[$j]->comments;
	if ($ArrayOne[$j]->defectReportId != null) $ArrayNew[$j]["defectReportId"] = $ArrayOne[$j]->defectReportId;
	if ($ArrayOne[$j]->blockedReason != null) $ArrayNew[$j]["blockedReason"] = $ArrayOne[$j]->blockedReason;

*/
	$out = $out . json_encode($ArrayNew[$j]);

}

//print_r($ArrayNew);
echo $out;

?>

