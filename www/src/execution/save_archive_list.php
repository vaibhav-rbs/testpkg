<?php
include('post_processor.php');
include('remote_common.php');



$server = $_REQUEST['name'];
$device = $_REQUEST['device'];
$status = $_REQUEST['status'];
$runlist = $_REQUEST['runlist'];

//$server = "jing";
//$device = "0A3BC2B50E01B00C";
//$status = "Ready To Be Used";
//$runlist = "crmg76_demo_product - (MD Advance Platforms) Cycle 1.xml";

$dev_runlist = $device . "_" . $runlist;

$user = "autotest";
$pass = "autotest";

$string = file_get_contents("../../tempdata/testservers.json");
$json_a = json_decode($string, true);
$row_array = $json_a["rows"];
$num = count($row_array);


if ($server != null){
	for ($i=0; $i < $num ; $i++){
		if($row_array[$i]["name"] == $server){
			$controller = $row_array[$i]["ip"]; 
			$home = $row_array[$i]["home"];
			break;
		}
	}
	sleep(5);
	$result_dir = "";
        for ($i = 0; $i < 100 ; $i++){
		sleep(5);
		if($result_dir == ""){
			$target_dir = "results_" . $device . "_" . $dev_runlist;
			$cmd = "cd $home;ls | grep '$target_dir'";
			$ret = remote_exec($cmd,$controller,$user,$pass);
			$result_dir = rtrim($ret);
		}else{

			$save_string = $home . ":" . $controller . ":" . $result_dir;
			$fp = fopen('/datafiles/logfiles/need_archive_list.txt', 'a');
		        if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock
				fwrite($fp, $save_string . "\n");
                		fflush($fp);            // flush output before releasing the lock
                		flock($fp, LOCK_UN);    // release the lock
        		} else {
                		log_line("Failed to retrive lock --" . $save_string);
        		}
			fclose($fp);
			break;
		}
	}
	echo json_encode(array('msg'=>'success'));

} else {
	echo json_encode(array('msg'=>'No server name is provided'));
}

function log_line($str){

	$dtime = date("m-d-y H:i:s");
	$target_str = $dtime . "::" . $str . "\n";
	$fp = fopen('/datafiles/logfiles/agent.log', 'a');
	fwrite($fp, $target_str);
	fclose($fp);
}




?>
