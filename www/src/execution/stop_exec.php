<?php

include('remote_common.php');


$server = $_REQUEST['name'];
$device = $_REQUEST['device'];

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

	$cmd = "grep $device ../../tempdata/*.comp| wc -l";
	$ret = exec($cmd);
	$ret = chop($ret);
	if($ret > 0){
                echo json_encode(array('msg'=>'This device is a companion, please stop processes from target device'));
                exit(0);


	}



        $ping_value = exec("ping -c 2 $controller | grep  -w \"0% packet loss\" | wc -l");
        if($ping_value != "1"){
                echo json_encode(array('msg'=>'Not able to ping test server'));
                exit(0);
        }


	$cmd = "cd $home; ./stop.sh $device";
	$ret = remote_exec($cmd,$controller,$user,$pass);

	$cmd = "ps aux | grep start.sh |grep $device |grep -v grep | wc -l";
	$ret = remote_exec($cmd,$controller,$user,$pass);
	$check_start_process = rtrim($ret);

	$flag = 0;
	if ($check_start_process != "0"){
        	for($i = 0 ; $i < 120 ; $i++){
                	$cmd = "ps aux | grep start.sh |grep $device |grep -v grep | wc -l";
                	$ret = remote_exec($cmd,$controller,$user,$pass);
                	$check_start_process = rtrim($ret);
                	if ($check_start_process != "0"){
                        	$flag = 0;
                        	sleep(1);
                	}else{
                        	$flag = 1;
                        	break;
                	}
        	}
	}else{
        	$flag = 1;
	}

	if ($flag == 1){
		echo json_encode(array('success'=>true));
	}else{
        	echo json_encode(array('msg'=>'Process is not cleared on Test Machine, please check...'));
	}

} else {
	echo json_encode(array('msg'=>'Receive no server name.'));
}

?>
