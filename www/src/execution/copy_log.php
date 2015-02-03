<?php


$user_name = $_REQUEST['user_name'];
$log_file  = $_REQUEST['log_file'];

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

if($log_file != NULL){
	list($first, $second) = split(".xml",$log_file);
	list($first, $target) = split("/", $second);


	$dest = "../../tempdata/log_data/" . $user_name . "/" . $target;
	$command = "cp '" . $log_file . "' " . "'" . $dest . "'";
	exec($command);
}
echo json_encode(array('success'=>true));
?>
