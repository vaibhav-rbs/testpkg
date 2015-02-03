<?php
require_once 'SOAP/Client.php'; 
include('../common/tc_functions.php');
include '../db/api.php';

$userName = $_GET['user_name'];
//$userName = 'fbxj76';
$nodeId = isset($_POST['id']) ? trim($_POST['id']) : '';
//$nodeId = "group^Emerging Communications Group|folder^28450 Digital Media Server";

// load groups
if ($nodeId == '') {
	getGroupsJson();
} else {
	// if group node is clicked, get functional area folders
	$arrId = preg_split('/\|/', $nodeId);
	
	foreach ($arrId as $id) {
		list($class, $text) = preg_split('/\^/', $id);
		
		switch ($class) {
			case 'group':
				$groupName = $text;
				break;
			case 'folder':
				$folderName = $text;
				break;
			case 'suite':
				$suiteName = $text;
				break;
		}
	}
	
	isset($suiteName) ? getTestCasesBySuite($suiteName) : getFuncAreaFolders($groupName, $class, $folderName);  
}

function getGroupsJson(){
	global $userName;
	$xml = simplexml_load_string(GetAllGroupsByCoreid($userName));

	if(count($xml->Table) > 0){
		$result = array();

		foreach ($xml->Table as $tableList){
			$groupName = trim($tableList->groupName);
			$node = array('id' => "group^$groupName", 'text' => $groupName, 'state' => 'closed');			
			array_push($result, $node);
		}

		echo json_encode($result);
	}
}

function getFuncAreaFolders($group, $class, $folder) {
	global $userName;
	$xml = simplexml_load_string(Get_Test_CaseSuite_By_GroupName($userName));
	
	if (count($xml->Table) > 0) {
		$result = array();
		
		foreach ($xml->Table as $tableList) {
			if (trim($tableList->groupName == $group)) {
				list($funcArea, $suiteNum) = preg_split('/:/', trim($tableList->testSuiteName));

				switch ($class) {
					case 'group':
						$folders = preg_split('/\./', $funcArea);
						$node = array('id' => "group^$group|folder^$folders[0]", 'text' => $folders[0], 'state' => 'closed');
						if (!in_array($node, $result)) {
							array_push($result, $node);	
						} 
						break;
					case 'folder':
						if ($folder == $funcArea) {
							$node = array('id' => "suite^$folder:$suiteNum", 'text' => $suiteNum, 'state' => 'closed', 'attributes' => '');
							if (!in_array($node, $result)) {
								array_push($result, $node);	
							}
							/*
							// get test suites by functional area
							$xml = simplexml_load_string(Get_Test_Suite_By_Functional_Area($funcArea));
							foreach ($xml->Table as $table) {
								$node = array();
								foreach ($table->children() as $child) {
									$key = trim((string)$child->getName());
									$value = trim((string) $child);
									
									switch ($key) {
										case 'TestSuiteName':
											list($funcArea, $num) = preg_split('/:/', $value);
											$node['id'] = "suite^$value";
											$node['text'] = $num;
											$node['state'] = 'closed';
											break;
										case 'TPSHeaderValue':
											$headers = array();
											foreach ($child->XMLDATA->COLUMNS->Column as $columns) {
												array_push($headers, $columns['name']);
											}
											$node['attributes']['headers'] = implode(',', $headers);			
											break;
										default:
											$node['attributes'][$key] = $value;
											break;
									}
								}
								
								if (!in_array($node, $result)) {
									array_push($result, $node);
								}
							}
							*/
						} else if (preg_match("/$folder/", $funcArea)) {
							$funcArea = str_replace("$folder.", '', $funcArea);
							$folders = preg_split('/\./', $funcArea);
							$node = array('id' => "group^$group|folder^$folder.$folders[0]", 'text' => $folders[0], 'state' => 'closed');
							if (!in_array($node, $result)) {
								array_push($result, $node);	
							}
						}
						break;
				}
			}
		}
		
		echo json_encode($result);
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
			
			// change the icon to script for automated test cases - Jungsoo
			/*if ($tableList->ExecutionMethodName == "Automated") {
				$node['iconCls'] = 'icon-script';
			}*/
			
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

function getFuncAreaJson($coreid, $group) {
	$xml = simplexml_load_string(Get_Test_CaseSuite_By_GroupName($coreid));
	
	if (count($xml->Table) > 0) {
		$result = array();
		
		foreach($xml->Table as $list) {
			$node = array();
			
			if ($list->groupName == $group) {
				$testSuite = preg_split("/:/", trim($list->testSuiteName));
				$funcArea = $testSuite[0];
				$suiteNum = $testSuite[1];
				
				// get previous functional area
				$size = sizeOf($result);
				
				// create node for new functional area
				if ($funcArea != $result[$size - 1]['text']) {
					$node['id'] = 'fa^' . $group . '^' . $funcArea;
					$node['text'] = $funcArea;
					$node['state'] = 'closed';
					
					array_push($result, $node);	
				}
			}
		}
		
		echo json_encode($result);
	}
}

function getTestSuiteJsonByFuncArea($group, $funcArea) {
	$xml = simplexml_load_string(Get_Test_Suite_By_Functional_Area($funcArea));

	if(count($xml->Table) > 0){
		$result = array();
	
		foreach($xml->Table as $tableList){
			$node = array();

			$groupName = trim($tableList->GroupName);
			if ($groupName == $group) {
				// update column_by_suite JSON file

/*
				if (strlen(trim($tableList->TPSHeaderValue)) > 0) {
					update_to_TPS_file(trim($tableList->TPSHeaderValue), trim($tableList->TestSuiteName), $groupName);
				}

*/
				
				$testSuite = preg_split("/:/", trim($tableList->TestSuiteName));
				$suiteNum = $testSuite[1];
				$node['id'] = 'suite^'.trim($tableList->TestSuiteName);
				$node['text'] = $suiteNum;
				$node['state'] = 'closed';	
				$node['attributes'] = getTPSHeaderValues($groupName . '^' . trim($tableList->TestSuiteName));
				//$node['attributes'] = trim($tableList->TPSHeaderValue);
				array_push($result, $node);
			}
		}
	
		echo json_encode($result);
	}	
}

function getTPSHeaderValues($testSuiteName) {
	$jsonStr = file_get_contents("/datafiles/logfiles/columns_by_suite.json");
	$jsonArr = json_decode($jsonStr, true);
	
	return (string)$jsonArr[$testSuiteName]; 
}
/*
function update_to_TPS_file($columns, $suite_name, $group_name){

	$key = $group_name . "^" . $suite_name;
	$value = $columns;

	$file_saved = "/datafiles/logfiles/columns_by_suite.json";
	if(file_exists($file_saved)){
		$fp = fopen($file_saved, "r+");
                while (1) {
			if(flock($fp, LOCK_EX)) {  // acquire an exclusive lock
                        	$column_string = fread($fp, filesize($file_saved));
				$column_arr = json_decode($column_string, true);
				$column_arr[$key] = $value;
                        	ftruncate($fp, 0);      // truncate file
				rewind($fp);
				fwrite($fp, json_encode($column_arr));
                        	flock($fp, LOCK_UN);    // release the lock
				fclose($fp);
				break;
			}
			sleep(1);
                }

	}else{
		$column_arr = array();
		$column_arr[$key] = $value;
		$fp = fopen($file_saved, 'w');
		fwrite($fp, json_encode($column_arr));
		fclose($fp);
	}
}
*/


?>