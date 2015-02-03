<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP

//session_start();
//$user_name = $_SESSION['username'];

$cycleplan=$_REQUEST['name'];

//$cycleplan="Kukak - IR13 System Test Middleware (MD Advance Platforms) Cycle 1";

$retResult = Get_Test_Case_Info($cycleplan);
$retResult1 = Get_Test_Case_His_Result($cycleplan);
$retResult2 = Get_Soft_Hard_Ver($cycleplan);
$retResult3 = Get_Plan_Details($cycleplan);
$xml= simplexml_load_string($retResult);
$xml1= simplexml_load_string($retResult1);
$xml2= simplexml_load_string($retResult2);
$xml3= simplexml_load_string($retResult3);


$count = count($xml);

If ($count == 0){
        echo json_encode(array('msg'=>'Empty info from Test Central'));
        exit(0);
}



$created_date = $xml3->Table->CreatedDate;
$last_update  = $xml3->Table->LastUpdDate;

list($date, $right) = split("T", $created_date);
list($time, $other) = split("\.", $right);
$created_date = $date . " " . $time;


list($date, $right) = split("T", $last_update);
list($time, $other) = split("\.", $right);
$last_update = $date . " " . $time;


$reArray = array();

$reArray["cplan"] = $cycleplan;
if(isset($xml2->Table->PlanDetailXML->XMLDATA->Column[0])){
	$software_ver = $xml2->Table->PlanDetailXML->XMLDATA->Column[0];
	$reArray["software_ver"] = $software_ver;
}
if(isset($xml2->Table->PlanDetailXML->XMLDATA->Column[1])){
	$hardware_ver = $xml2->Table->PlanDetailXML->XMLDATA->Column[1];
	$reArray["hardware_ver"] = $hardware_ver;
}
$reArray["created_date"] = $created_date;
$reArray["last_update"] = $last_update;
$reArray["detail"] = $xml;
$reArray["detail_defect"] = $xml1;


echo json_encode($reArray);

// Get info of test cases of latest update based on plan
// Has caseDescription, groupType, testResult, lastUpdDate.
function Get_Test_Case_Info($plan)
{   
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl); 
        $executionServiceClient   = $executionServiceWsdl->getProxy(); 
        $executionServiceClient->setOpt('timeout', 200);
        $ret = $executionServiceClient->Interface_GetTestCaseInfoByPlan($plan);
        
        return $ret; 
}
	
// Get info of history of result of test cases based on plan 
// Has defectReportId, blockedReason, groupType, testResult, execTime, lastUpdDate
function Get_Test_Case_His_Result($plan)
{   
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_executionService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl); 
        $executionServiceClient   = $executionServiceWsdl->getProxy(); 
        $executionServiceClient->setOpt('timeout', 200);
        $ret = $executionServiceClient->Interface_GetTestResultHistoryByTestPlanName($plan);
        
        return $ret; 
}


// Get software and hardware versions
function Get_Soft_Hard_Ver($plan)
{   
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl); 
        $executionServiceClient   = $executionServiceWsdl->getProxy(); 
        $executionServiceClient->setOpt('timeout', 200);
        $ret = $executionServiceClient->Interface_GetTestPlanDetailInformation($plan);
        
        return $ret; 
}


// Get created date and last updated date of cycle plan	
function Get_Plan_Details($plan)
{   
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl); 
        $executionServiceClient   = $executionServiceWsdl->getProxy(); 
        $executionServiceClient->setOpt('timeout', 200);
        $ret = $executionServiceClient->Interface_GetPlanDetails($plan);
        
        return $ret; 
}
	
	
?>
