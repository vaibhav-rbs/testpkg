<?php
require_once 'SOAP/Client.php';
include('../common/app_log.php');


$corid = $_REQUEST['user_name'];


$nodeID = isset($_POST['id']) ? trim($_POST['id']) : getGroupsJson($corid);

app_log_line("nodeID = " . $nodeID);


if(strpos($nodeID, "group^") !== false){
	$groupName = substr($nodeID, strpos($nodeID, "^") + 1);
	app_log_line("groupName = " . $groupName);
	getMasterPlanJson($groupName);
}
elseif (strpos($nodeID, "master^") !== false){
	$masterName = substr($nodeID, strpos($nodeID, "^") + 1);
	app_log_line("masterName = " . $masterName);
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
		app_log_line("From getGroupsJson = " . json_encode($result));	
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

		app_log_line("From getMasterPlanJson = " . json_encode($result));	
		echo json_encode($result);
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
			//$node['state'] = 'closed';
				
			array_push($result, $node);
		}
		
		app_log_line("From getCyclePlanJson = " . json_encode($result));	
		echo json_encode($result);
	}
}
/*
function getTestCasesJson($cycleName){
	$xml = simplexml_load_string(getTestCases($cycleName));
	
	if(count($xml->Table) > 0){
		$result = array();
		
		foreach ($xml->Table as $tableList){
			$node = array();
			
			$node['id'] = trim($tableList->testCaseName);
			
			// pass = green, fail = red, indetermine = blue, block = yellow, otherwise, no color
			switch (trim($tableList->testResult)){
				case 'F':
					$node['text'] = trim($tableList->testCaseName) . ' <span style="background-color:grey;color:red;">&nbsp;Fail&nbsp;</span> - ' . trim($tableList->caseDescription);
					break;
				case 'B':
					$node['text'] = trim($tableList->testCaseName) . ' <span style="background-color:grey;color:yellow;">&nbsp;Block&nbsp;</span> - ' . trim($tableList->caseDescription);
					break;
				case 'I':
					$node['text'] = trim($tableList->testCaseName) . ' <span style="background-color:grey;color:blue;">&nbsp;Indetermine&nbsp;</span> - ' . trim($tableList->caseDescription);
					break;
				case 'P':
					$node['text'] = trim($tableList->testCaseName) . ' <span style="background-color:grey;color:lime;">&nbsp;Pass&nbsp;</span> - ' . trim($tableList->caseDescription);
					break;
				default:
					$node['text'] = trim($tableList->testCaseName) . ' <span style="background-color:grey;color:silver;">&nbsp;Not Run&nbsp;</span> - ' . trim($tableList->caseDescription);
			}
			
			// check for the script files existence
			$scriptfile = "/datafiles/testscriptfiles/" . trim($tableList->testCaseName) . ".xml";
			
			if(fopen($scriptfile, 'r')){
				if(filesize($scriptfile) > 0){
					$node['iconCls'] = 'icon-script';
				} else {
					$node['iconCls'] = 'icon-problem';
				}
			} else {
				$node['iconCls'] = 'icon-treefile';
			}
			
			fclose($scriptfile);
			
			array_push($result, $node);
		}
		
		sort($result);
		app_log_line("From getTestCasesJson = " . json_encode($result));	
		
		echo json_encode($result);
	}
}

*/

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
?>
