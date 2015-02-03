<?php
require_once 'SOAP/Client.php'; 
include '../db/api.php';

$user_name = $_GET['user_name'];
$nodeID = isset($_POST['id']) ? trim($_POST['id']) : getGroupsJson($user_name);
//$nodeID = 'master^XFON MASTER - WISL Stability Rack (MD Android WISL)';
#$nodeID = 'cycle^DVX GPSE - BT_Regression (MD Android WISL) Cycle 1';
//$nodeID = "group^MD Android WISL";

//if(isset($_POST['text']))
//	$nodeText = $_POST['text'];

if(strpos($nodeID, "group^") !== false){
	$groupName = substr($nodeID, strpos($nodeID, "^") + 1);
	getMasterPlanJson($groupName);
}
elseif (strpos($nodeID, "master^") !== false){
	$masterName = substr($nodeID, strpos($nodeID, "^") + 1);
	$arr_testcases = getTestCasesJson($masterName);
	
	if (count($arr_testcases) > 0) {
		echo json_encode($arr_testcases);
	} else {
		getCyclePlanJson($masterName);	
	}
}
elseif (strpos($nodeID, "cycle^") !== false){
	$cycleName = substr($nodeID, strpos($nodeID, "^") + 1);
	echo json_encode(getTestCasesJson($cycleName));
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
			$masterplan = trim($tableList->testplanname);
			
			if(preg_match('/.*?MASTER/', $masterplan, $matches)) {
				$product = trim(str_replace("MASTER", "", $matches[0]));
				
				$index = search_node($result, $product);
				
				// for a new product, add product node
				if($index === NULL) {
					$node['id'] = $product;
					$node['text'] = $product;
					$node['state'] = 'closed';
					$node['children'] = array(array('id' => "master^" . $masterplan, 'text' => $masterplan, 'state' => 'closed'));
					array_push($result, $node);	
				} else {
					// for the existing product node, update its children of master plans
					$children = $result[$index]['children'];
					array_push($children, array('id' => "master^" . $masterplan, 'text' => $masterplan, 'state' => 'closed'));
					$result[$index]['children'] = $children;
				}
			}
		}

		echo json_encode($result);
	}
}

/**
 * search_node: search node by its id
 * @param $array - target array
 * @param $id - id of the node to find
 */
function search_node($array, $id) {
	foreach($array as $index => $item) {
		if($item['id'] == $id) {
			return $index;
		}
	}
} 

function getCyclePlanJson($masterName){
	$xml = simplexml_load_string(getCyclePlans($masterName));
	//$masterMask = preg_split("/ /", $masterName);

	if(count($xml->Table) > 0){
		$result = array();

		foreach ($xml->Table as $tableList){
			$cycleplan = trim($tableList->testplanname);
			list($junk,$cycle) = split(") ",$cycleplan);
			//$cycle = trim(str_replace($masterMask, "", $cycleplan));
			
			$node = array();
			$node['id'] = 'cycle^' . $cycleplan;
			$node['text'] = $cycle;
			$node['state'] = 'closed';
				
			array_push($result, $node);
		}
		
		echo json_encode($result);
	}
}

function getTestCasesJson($planname){
	$xml = simplexml_load_string(getTestCases($planname));
	
	if(count($xml->Table) > 0){
		$result = array();
		
		foreach ($xml->Table as $tableList){
			$testcase = trim($tableList->testCaseName);
			$node = array();
			$node['id'] = 2;
			$node['text'] = $testcase;
			$node['attributes']['description'] = trim($tableList->caseDescription);
			$node['attributes']['scriptPath'] = '';
			$node['attributes']['gitUrl'] = '';
			$node['iconCls'] = '';
			
			// retrieve script path
			$arr_path = get_script_path($testcase);
			
			if (count($arr_path) == 1) {
				$node['attributes']['scriptPath'] = trim($arr_path[0]['script_path']);
				
				if (strlen(trim($arr_path[0]['script_path'])) > 0) {
					$node['iconCls'] = 'icon-script';
					$node['attributes']['gitUrl'] = trim($arr_path[0]['git_url']);
				}
			}
			
			if (!in_array($node, $result)) {
				array_push($result, $node);
			}
		}
		
		# sort result array by test case name
		function cmp($a, $b) {
			return strcmp($a['text'], $b['text']);
		}
		
		usort($result, "cmp");

		return $result;
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

function getTestCases($planName){
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestCaseInfoByPlan($planName);
	return $executionHistory;
}
?>