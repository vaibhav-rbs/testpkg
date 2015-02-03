<?php
require_once 'SOAP/Client.php';
//$planName = "Xfon ATT - 4.4 Regression Test plan (MD Android Sys_Test) Cycle 4";
$planName = "Xfon ATT MASTER - 4.4 Regression Test plan (MD Android Sys_Test)";

echo getTestCases($planName);

function getTestCases($planName){
	//$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_executionService.asmx?WSDL';
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	//$executionHistory = $executionServiceClient->Interface_GetTestResultHistoryByTestPlanName($planName);
	$executionHistory = $executionServiceClient->Interface_GetTestCaseInfoByPlan($planName);
	
	return $executionHistory;
}
?>