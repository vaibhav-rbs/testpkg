<?php


$user_name = $_REQUEST['user_name'];
$log_file  = $_REQUEST['log_file'];

//$user_name = "crmg76";
//$log_file  = "/datafiles/logfiles/logs/results_0A3BC2B50E01B00C_0A3BC2B50E01B00C_crmg76_demo_product - (MD Advance Platforms) Cycle 2.xml_2012_05_15_15_31_01/MDB APENG.ARIA.BAT:009-003 05-15-12 15:31:01.122.png";

//Make sure log_data dir is exist

$log_dir = "../../tempdata/log_data";

if(!is_dir($log_dir)){
	$make_dir = "mkdir ../../tempdata/log_data";
	exec($make_dir);
}

//Make sure user log dir is exist

$user_dir = "../../tempdata/log_data/" . $user_name;
if(!is_dir($user_dir)){
	$make_user_dir = "mkdir ../../tempdata/log_data/" . $user_name;
	exec($make_user_dir);
}

if($log_file != ""){
	list($first, $second) = split(".xml",$log_file);
	list($first, $target) = split("/", $second);
	$dest = "../../tempdata/log_data/" . $user_name . "/" . $target;
	$command = "cp '" . $log_file . "' " . "'" . $dest . "'";
	exec($command);
	echo json_encode(array('success'=>true));
}else{
	echo json_encode(array('success'=>false));
}

?>
