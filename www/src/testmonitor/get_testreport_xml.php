<?php
require_once 'SOAP/Client.php';

//$masterplan = "XFON MASTER - testing test depot (MD Android Sys_Test)";
//getTestResults($masterplan);
//exit(0);

isset($_REQUEST['masterplan']) ? getTestResults($_REQUEST['masterplan']) : getMasterPlan();

function getTestResults($masterplan) {
	$result = array();
	$testreports = scandir('/datafiles/testresult');
	
	for($i = 2; $i < count($testreports); $i++) {
		$fileTestResult = $testreports[$i];
		
		if (strpos($fileTestResult, "_testjob_$masterplan") !== false) {
			$temp = str_replace("_testjob_$masterplan", '', $fileTestResult);
			$arrTemp = preg_split('/_/', $temp);
			
			array_pop($arrTemp);
			
			$loop = array_pop($arrTemp);
			$device = array_shift($arrTemp);
			$time = array_pop($arrTemp);
			$scope = implode($arrTemp);
			
			$option = array('id' => $fileTestResult, 
							'device' => '<img src="themes/icons/stop_16.png" style="vertical-align:middle;"> '.$device, 
							'scope' => '<img src="themes/icons/find_in_file_16.png" style="vertical-align:middle;"> '.$scope, 
							'time' => '<img src="themes/icons/clock_14.png" style="vertical-align:middle;"> '.$time,
							'loop' => '<img src="themes/icons/repeat_16.png" style="vertical-align:middle;"> '.$loop);
			
			if (!in_array($option, $result)) {
				array_push($result, $option);
			}
		}
	}
	
	sort($result);
	echo json_encode($result);
}

function getMasterPlan() {
	$result = array();
	$testreports = scandir('/datafiles/testresult');
	$header = array('id' => 0, 'text' => '--Select master plan--', 'selected' => 'true'); 
	
	for($i = 2; $i < count($testreports); $i++) {
		$fileTestResult = $testreports[$i];
		
		if (preg_match('/_testjob_.*\(.*?\)/', $fileTestResult, $matches)) {
			$masterplan = preg_replace('/_testjob_/', '', $matches[0]);
			$option = array('id' => $masterplan, 'text' => $masterplan);
			
			if (!in_array($option, $result)) {
				array_push($result, $option);
			}
		}
	}
	
	sort($result);
	array_unshift($result, $header);
	echo json_encode($result);	
}
?>