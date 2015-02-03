<?php
include('post_processor.php');

$execlist = $_REQUEST['execlist'];
$execlist = chop($execlist);
//$execlist = "results_0A3BC2B80900900D_0A3BC2B80900900D_crmg76_Kukak - Invader Demo (MD Advance Platforms) Cycle 1.xml_2012_02_02_15_47_25";

$json_testcase = array(); // json array to be returned
$row_array = array();

//Clean up tempdata/log_data/<corid>
$arr_tmp = split("_", $execlist);
$corid = $arr_tmp[3];

$cmd = "ls ../../tempdata/log_data/" . $corid . "/ | wc -l";
$ret = exec($cmd);
$ret = chop($ret);
if ($ret > 0 ){
	$cmd = "rm ../../tempdata/log_data/" . $corid . "/*";
	exec($cmd);
}
//End clean up


$processor = new post_processor();
$return_array0 = $processor -> get_log_array($execlist); //create original array from log dir
$return_array = $processor -> get_log_array_for_TC($return_array0);

//print_r($return_array);

$return_test = $return_array["test_array"];
$num = count($return_test);
for ($i=0; $i < $num; $i++){
	$row_array[$i]["test_case_name"] = $return_test[$i]["test_case_name"];
	$row_array[$i]["test_result"] = $return_test[$i]["test_result"];
	$row_array[$i]["exec_time"] = $return_test[$i]["exec_time"];
	$row_array[$i]["result_dir"] = $execlist;
}

$json_testcase["rows"] = $row_array;
$json_testcase["total"] = $num;





echo json_encode($json_testcase);


?>
