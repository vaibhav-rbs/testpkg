<?php
include('post_processor.php');
include('remote_common.php');


$server = $_REQUEST['name'];
$device = $_REQUEST['device'];
$runlist = $_REQUEST['runlist'];

//$server = "jing";
//$device = "0A3BC2B50E01B00C";
//$runlist = "crmg76_demo_product - (MD Advance Platforms) Cycle 1.xml";

$dev_runlist = $device . "_" . $runlist;




// Check existing of TARGET.comp file that contain info of companion

$target_file = "../../tempdata/" . $device . ".comp";
$path_runlist = "/datafiles/runlistfiles/" . $runlist;

$cmd = "grep COMPANION_DEV '$path_runlist' | wc -l";  
$comp_num = exec($cmd);



if ($comp_num > 0){
	if(file_exists($target_file)){
		echo json_encode(array('success'=>true));
	}else{

		echo json_encode(array('msg'=>'Missing information of companion device ID, please click "Set Up Companion" button to set it up'));
	}
}else{
	echo json_encode(array('success'=>true));
}

?>
