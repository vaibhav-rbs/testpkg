<?php
require_once 'SOAP/Client.php';
include('../common/tc_functions.php');
include '../db/api.php';

$userName = $_POST['username']; 
$search = $_POST['search'];

if (isset($_POST['search'])) {
	searchPlan($userName, $search);
} else {
	$id = $_POST['id'];
	
	if (preg_match('/^master\^/', $id)) {
		$masterPlan = preg_replace('/^master\^/', '', $id);
		$arrTestCases = getTestCasesJson($masterPlan);
		
		if (count($arrTestCases) > 0) {
			echo json_encode($arrTestCases);
		} else {
			getCyclePlanJson($masterPlan);
		}
	} else if (preg_match('/^cycle\^/', $id)) {
		$cyclePlan = preg_replace('/^cycle\^/', '', $id);
		echo json_encode(getTestCasesJson($cyclePlan));
	}
}

/*******************************************************
 * Functions
 */
function searchPlan($userName, $search) {
	$result = array();
	$list = array();
	$xmlGroup = simplexml_load_string(GetAllGroupsByCoreid($userName));

	foreach ($xmlGroup->Table as $tableGroup) {
		$group = trim($tableGroup->groupName);
		$xmlMaster = simplexml_load_string(Get_Test_Plans('Master Plan', $group));

		foreach ($xmlMaster->Table as $tableMaster) {
			$masterPlan = trim($tableMaster->testplanname);
			
			if (preg_match('/'.$search.'/', $masterPlan)) {
				
				// split master plan by product
				if (preg_match('/.*?MASTER/', $masterPlan, $matches)) {
					$product = trim(str_replace("MASTER", '', $matches[0]));
				}
				
				array_push($list, "$group^$product^$masterPlan");
			}
		}
	}
	
	foreach ($list as $value) {
		$folders = preg_split('/\^/', $value);
		
		$arrPath = array();
		foreach ($folders as $index => $folder) {
			array_push($arrPath, $folder);
			
			// first folder is group and last folder is master plan
			// we need an IDs for those folders
			if ($index == 0) {
				$node = array('id' => "group^$folder", 'text' => $folder, 'children' => array(), 'state' => 'closed');
			} else if ($index == count($folders) - 1) {
				$node = array('id' => "master^$folder", 'text' => $folder, 'children' => array(), 'state' => 'closed');	
			} else {
				$node = array('id' => $folder, 'text' => $folder, 'children' => array(), 'state' => 'closed');
			}

			pushToTree($result, $node, $arrPath);
		}
	}
	
	echo json_encode($result);
}

function pushToTree(&$array, $node, $arrPath) {
	
	// the only left is the node itself to push to tree array
	// if the node itself does not exist, push it to the tree array
	if (count($arrPath) == 1) {
		foreach ($array as $item) {
			if ($item['text'] == $node['text']) {
				return;
			}
		}
		array_push($array, $node);
		return;
	}

	$search = array_shift($arrPath);

	foreach ($array as &$item) {
		if ($item['text'] == $search) {
			pushToTree($item['children'], $node, $arrPath);
		}
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
		
		//echo json_encode($result);
		return $result;
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
?>