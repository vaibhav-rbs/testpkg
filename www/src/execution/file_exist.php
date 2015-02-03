<?php 

//$username = "crmg76";
//$file = "../../tempdata/single_log_data/crmg76_err.txt";

$username = $_REQUEST['username'];
$file = $_REQUEST['file'];

if (file_exists($file)){
	echo json_encode(array('msg'=>"success"));
}else{
	echo json_encode(array('msg'=>'No log file found!'));
}
?>
