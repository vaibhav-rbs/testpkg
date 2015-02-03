<?php
require_once 'SOAP/Client.php'; 
include('../common/tc_functions.php');
include '../db/api.php';

$userName = $_POST['username']; 
$search = $_POST['search'];

if (isset($_POST['search'])) {
	searchSuite($userName, $search);
} else {
	$id = $_REQUEST['id'];
	if (preg_match('/suite\^/', $id)) {
		getTestCasesBySuite(preg_replace('/suite\^/', '', $id));
	}	
}

/*********************************************
 * Functions
 */

function searchSuite($userName, $search) {
	$result = array();
	$list = array();
	$xml = simplexml_load_string(Get_Test_CaseSuite_By_GroupName($userName));
	
	foreach ($xml->Table as $table) {
		$suiteName = trim($table->testSuiteName);
		$groupName = trim($table->groupName);
		
		if (preg_match('/'.$search.'/', $suiteName)) {
			array_push($list, "$groupName.$suiteName");
		}
	}
	
	foreach ($list as $value) {
		list($funcArea, $suiteNum) = preg_split('/:/', $value);
		$folders = preg_split('/\./', $funcArea);
	
		$arrPath = array();
		foreach ($folders as $index => $folder) {
			array_push($arrPath, $folder);
			
			// make a path for each node when adding to tree
			if ($index == 0) {
				$node = array('id' => "group^$folder", 'text' => $folder, 'children' => array(), 'state' => 'closed');
			} else {
				// get group
				$group = $arrPath[0];
				$path = implode('.', array_slice($arrPath, 1));
				$node = array('id' => "group^$group|folder^$path", 'text' => $folder, 'children' => array(), 'state' => 'closed');
			}
			
			pushToTree($result, $node, $arrPath);
		}
	
		array_push($arrPath, $suiteNum);
		
		// get full suite name
		$suitePath = implode('.', (array_slice($arrPath, 1, -1))) . ":$suiteNum";
		$node = array('id' => "suite^$suitePath", 'text' => $suiteNum, 'children' => array(), 'state' => 'closed');
		pushToTree($result, $node, $arrPath);
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

function getTestCasesBySuite($suite){
	$xml = simplexml_load_string(Get_Test_Case_By_Suite($suite));
	
	if(count($xml->Table) > 0){
		$result = array();
		
		foreach ($xml->Table as $tableList){
			$testcase = trim($tableList->TestCaseName);
			$node = array();
			$node['id'] = 2;
			$node['text'] = $testcase;
			$node['attributes']['description'] = trim($tableList->CaseDescription);
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
		
		echo json_encode($result);
	}
}
?>