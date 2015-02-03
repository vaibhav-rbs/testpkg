<?php 

//function to get all the MASTER PLANS from Test Central
function getCyclePlans($planName){
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestPlans("Cycle Plan","Testing","and tp.parentDetail = '" . $planName . "' order by Cycle asc");
	return $executionHistory;
}

function getTestCases($planName){
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestCaseInfoByPlan($planName);
	return $executionHistory;
}


function Get_Test_Plans($plan,$groupname)
{   
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestPlans($plan,"Testing","and tp.groupId in(select groupId from groups where groupName = '".$groupname."') order by TestPlanName asc");
	return $executionHistory;
}

function Get_Test_Suite_By_Functional_Area($functionalarea)
{   
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestSuitesByFunctionalArea($functionalarea);
	
	return $executionHistory; 
}

function Get_Test_Case_By_Suite($suite) {    
	//echo $suite;echo 'hardcode';
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 500);
	$executionHistory = $executionServiceClient->Interface_GetCaseGeneralInfo($suite);
	return $executionHistory;
}


function Get_Test_CaseDetails_By_TestCase($testcasename)
{
	//echo $suite;echo 'hardcode';
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 500);
	$executionHistory = $executionServiceClient->Interface_GetTestCaseDetailsByTestCase($testcasename);
	return $executionHistory;
}

function Get_Test_CaseSuite_By_GroupName($coreid)
{
	//echo $suite;echo 'hardcode';
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/Interface_executionService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 500);
	$executionHistory = $executionServiceClient->Interface_GetTestSuitesForGroupsByCoreId($coreid);
	//echo $executionHistory;
	return $executionHistory;
}

function GetAllGroupsByCoreid($coreid)
{

	 
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/Interface_executionService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetAllGroupsByCoreId($coreid);
	return $executionHistory;
}

function get_test_suite_general_info($suiteName){

	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
    $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
    $executionServiceClient   = $executionServiceWsdl->getProxy();
    $executionServiceClient->setOpt('timeout', 200);
    $return_data = $executionServiceClient->Interface_GetSuiteGeneralInfo($suiteName);

    return $return_data;
}
?>