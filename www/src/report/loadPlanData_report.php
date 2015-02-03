<?php
require_once 'SOAP/Client.php';

$corid = $_REQUEST['user_name'];


$nodeID = isset($_POST['id']) ? trim($_POST['id']) : getGroupsJson($corid);
//$nodeID = $_REQUEST['id'];

if(strpos($nodeID, "group^") !== false){
	$groupName = substr($nodeID, strpos($nodeID, "^") + 1);
	getMasterPlanJson($groupName);
}
elseif (strpos($nodeID, "master^") !== false){
	$masterName = substr($nodeID, strpos($nodeID, "^") + 1);
	getCyclePlanJson($masterName);
}

function getGroupsJson($coreid){
	$xml = simplexml_load_string(getGroupsByCoreid($coreid));
	
	if(count($xml->Table) > 0){
		$result = array();
		
		foreach ($xml->Table as $tableList){
			$node = array();
			
			$node['id'] = 'group^' . trim($tableList->groupName);
			$node['text'] = trim($tableList->groupName);
			$node['state'] = 'closed';
			
			array_push($result, $node);
		}
		
		echo json_encode($result);
	}
}

function getMasterPlanJson($groupName){
	$xml = simplexml_load_string(getMasterPlans($groupName));

	if(count($xml->Table) > 0){
		$result = array();

		foreach ($xml->Table as $tableList){
			$node = array();
				
			$node['id'] = 'master^' . trim($tableList->testplanname);
			$node['text'] = trim($tableList->testplanname);
			$node['state'] = 'closed';
				
			array_push($result, $node);
		}
		//break;

		echo json_encode($result);
	}
}

function getCyclePlanJson($masterName){
	$xml = simplexml_load_string(getCyclePlans($masterName));

	if(count($xml->Table) > 0){
		$result = array();

		foreach ($xml->Table as $tableList){
			$node = array();

			$node['id'] = trim($tableList->testplanname);
			$node['text'] = trim($tableList->testplanname);
				
			array_push($result, $node);
		}
		
		echo json_encode($result);
	}
}

function getMasterPlans($groupname){
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestPlans("Master Plan","Testing","and tp.groupId in(select groupId from groups where groupName = '" . $groupname . "') order by TestPlanName asc");
	return $executionHistory;
}

function getCyclePlans($planName){
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestPlans("Cycle Plan","Testing","and tp.parentDetail = '" . $planName . "' order by Cycle asc");
	return $executionHistory;
}

function getGroupsByCoreid($id){
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/Interface_executionService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetAllGroupsByCoreId($id);
	return $executionHistory;
}
?>
