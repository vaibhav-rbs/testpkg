<?php
include('post_processor.php');

$tname = $_REQUEST['tname'];
$resultdir = $_REQUEST['resultdir'];
//$resultdir = "results_0A3BC2B80900900D_0A3BC2B80900900D_crmg76_Kukak - Invader Demo (MD Advance Platforms) Cycle 1.xml_2012_02_02_15_47_25";

$json_step = array(); // json array to be returned
$row_array = array();



$processor = new post_processor();
$return_array0 = $processor -> get_log_array($resultdir); //create original array from log dir

//print_r($return_array0);

$return_test = $return_array0["test_array"];
$num = count($return_test);
$j = 0;
for ($i=0; $i < $num; $i++){
	if ($tname == $return_test[$i]["test_case_name"]){

		list($step_name, $ignore) = split(".log",$return_test[$i]["dev_log_name"]);
		$row_array[$j]["step"] = $step_name;
		$row_array[$j]["device"] = $return_test[$i]["device"];
		$row_array[$j]["device_type"] = $return_test[$i]["device_type"];
		$row_array[$j]["task"] = $return_test[$i]["task"];
		$row_array[$j]["result"] = $return_test[$i]["test_result"];

		$test_log_file = $return_test[$i]["test_log_name"];
		$dev_log_file = $return_test[$i]["dev_log_name"];
		$anr_log_file = $return_test[$i]["anr_log_name"];

		$test_log_path = "/datafiles/logfiles/logs/" . $resultdir . "/" . $test_log_file;
		$dev_log_path = "/datafiles/logfiles/logs/" . $resultdir . "/" . $dev_log_file;
		$row_array[$j]["tlog"] = $test_log_path;
		$row_array[$j]["dlog"] = $dev_log_path;
		$row_array[$j]["corid"] = $return_array0["corid"];
		$row_array[$j]["tfile"] = $test_log_file;
		$row_array[$j]["dfile"] = $dev_log_file;
		if ($anr_log_file == ""){
			$row_array[$j]["alog"] = "";
			$row_array[$j]["afile"] = "";
			
		}else{

			$anr_log_path = "/datafiles/logfiles/logs/" . $resultdir . "/" . $anr_log_file;
			$row_array[$j]["alog"] = $anr_log_path;
			$row_array[$j]["afile"] = $anr_log_file;

		}
		$j++;


	}
}

$json_step["rows"] = $row_array;
$json_step["total"] = $j;


echo json_encode($json_step);


?>
