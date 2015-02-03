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

$temp_arr = array();
$temp_arr = split("_", $runlist);
$username = $temp_arr[0];


$dev_runlist = $device . "_" . $runlist;
$target_file = "../../tempdata/" . $device . ".comp";
$path_runlist = "/datafiles/runlistfiles/" . $runlist;

$result_arr = array();
$content_arr = array();

function update_json_and_clean($username, $target_file, $device){

	$json_dev = array(); // json array to be returned
	$file3 = "../../tempdata/" . $username . "_device.json";
	$string = file_get_contents($file3);
	$json_dev = json_decode($string, true);
	$row_array = $json_dev["rows"];
	$num3 = count($row_array);

        if(file_exists($target_file)){

		$cmd = "cat " . $target_file; 
		$companion = exec($cmd);
		$companion = chop($companion);



		for ($i=0; $i < $num3 ; $i++){
			if($row_array[$i]["device"] == $companion){
				$json_dev["rows"][$i]["runlist"] = ""; 
				$json_dev["rows"][$i]["status"] = "Ready To Be Used"; 
				break;
			}
		}

                $cmd = "rm -f " . $target_file;
                exec($cmd);
        }
	for ($i=0; $i < $num3 ; $i++){
		if($row_array[$i]["device"] == $device){
			$json_dev["rows"][$i]["runlist"] = ""; 
			$json_dev["rows"][$i]["status"] = "Ready To Be Used"; 
			break;
		}
	}

       	$fp = fopen($file3, 'w');
	fwrite($fp, json_encode($json_dev));
	fclose($fp);
	

	return $json_dev;
}





if( $status != "Ready To Be Used" ){

	$content_arr = update_json_and_clean($username, $target_file,$device);
	$result_arr["content"] = $content_arr;
	$result_arr["msg"] = 'Device is in use: status=' . $status;
	echo json_encode($result_arr);
	exit(0);
}






// Check existing of property file for this TARGET device (This block will be removed, because property file should be checked before enter this program)

$property_file = "/datafiles/propertyfiles/" . $device . ".json";
if(!file_exists($property_file)){
	$content_arr = update_json_and_clean($username, $target_file, $device);
	$result_arr["content"] = $content_arr;
	$result_arr["msg"] = 'Missing property file for this device:' . $device . ', please set up property file by clicking "Edit Properties" button!';
	echo json_encode($result_arr);
	exit(0);
}


// Check existing of TARGET.comp file that contain info of companion 


$cmd = "grep COMPANION_DEV '$path_runlist' | wc -l";  
$comp_num = exec($cmd);



$runlist_string = file_get_contents($path_runlist);
if ($comp_num > 0){
	if(file_exists($target_file)){
		//replace COMPANION_DEV with ID
		$cmd = "cat " . $target_file; 
		$companion = exec($cmd);
		$companion = chop($companion);
		$runlist_string = str_replace("COMPANION_DEV", $companion, $runlist_string);
	}else{
		// This block will not be entered
		$content_arr = update_json_and_clean($username, $target_file, $device);
		$result_arr["content"] = $content_arr;
		$result_arr["msg"] = 'Missing information of companion device ID, please click "Set Up Companion" button to set it up';
		echo json_encode($result_arr);
		exit(0);

	}
}



//replace TARGET_DEV with ID
$runlist_string = str_replace("TARGET_DEV", $device, $runlist_string);



// Before copy over to remote test machine, let's replace the data in property file
// replace data in property file

$string_prop = file_get_contents($property_file);
$property_array = json_decode($string_prop, true);

$m_count = count($property_array);
for ($i = 0; $i < $m_count ; $i++){

         $key = $property_array[$i]["name"];
         $pos_key = strpos($runlist_string,$key);
         	if($pos_key === false) {
                          // key is not found in test script file
                }else{
                          $value = $property_array[$i]["value"];
                          if ($value == ""){
				$content_arr = update_json_and_clean($username, $target_file, $device);
				$result_arr["content"] = $content_arr;
				$result_arr["msg"] = "Missing value for " . $key . " in " . $runlist . ", Please correct your property file";
				echo json_encode($result_arr);
                                exit(0);

                          }else{
                                   $runlist_string = str_replace($key, $value, $runlist_string);
                          }
                }

}


$tmp_runlist = "/tmp/" . $dev_runlist;
$fp = fopen($tmp_runlist, 'w');
fwrite($fp, $runlist_string);
fclose($fp);
chmod($tmp_runlist, 0777);

// End of replacement




$user = "autotest";
$pass = "autotest";

list($core_user, $filename) = split ("_", $runlist);

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

	$ping_value = exec("ping -c 2 $controller | grep  -w \"0% packet loss\" | wc -l");
	if($ping_value != "1"){
		$content_arr = update_json_and_clean($username, $target_file, $device);
		$result_arr["content"] = $content_arr;
		$result_arr["msg"] = 'Ping test server failed';
		echo json_encode($result_arr);
                exit(0);
	}


        $cmd = "ps aux | grep start.sh |grep $device |grep -v grep | wc -l";
        $ret = remote_exec($cmd,$controller,$user,$pass);

        $check_start_process = rtrim($ret);

        if ($check_start_process != "0"){

		$content_arr = update_json_and_clean($username, $target_file, $device);
		$result_arr["content"] = $content_arr;
		$result_arr["msg"] = 'Device is in use';
		echo json_encode($result_arr);
                exit(0);

	}


	// Make sure there is no existing runlist.xml on test server related to this device and user
	$device_username = $device . "_" . $username;
	$cmd = "rm -f '$home/res/$device_username'*";
	$ret = remote_exec($cmd,$controller,$user,$pass);
	$cmd2 = "ls '$home/res/$device_username'*| wc -l";
	$ret = remote_exec($cmd2,$controller,$user,$pass);

	$check_runlist = rtrim($ret);

	if ($check_runlist != "0"){

		//echo "<script>alert(\"Not able to remove $home/res/runlist.xml on $controller. Please do chmod 777 runlist.xml\");</script>";
		$content_arr = update_json_and_clean($username, $target_file, $device);
		$result_arr["content"] = $content_arr;
		$result_arr["msg"] = 'Remove existing runlist failed';
		echo json_encode($result_arr);
                exit(0);


	}else{


		// Remote copy runlist to test server
		$connection = ssh2_connect($controller, 22);
		ssh2_auth_password($connection, $user, $pass);
		$remote_runlist = $home . "/res/$dev_runlist";
		ssh2_scp_send($connection, $tmp_runlist, $remote_runlist, 0644);


		// Make sure new runlist.xml exist on test server

		$cmd = "ls '$home/res/$dev_runlist'| wc -l";
		$ret = remote_exec($cmd,$controller,$user,$pass);
		$check_runlist = rtrim($ret);

		if ($check_runlist != "1"){

			//echo "<script>alert(\"Not able to copy runlist to $home/res/runlist.xml on $controller. Please check permission of $home/res\");</script>";
			$content_arr = update_json_and_clean($username, $target_file, $device);
			$result_arr["content"] = $content_arr;
			$result_arr["msg"] = 'Not able to copy runlist to remote server, please check permission of res dir';
			echo json_encode($result_arr);
                	exit(0);
		}else{

			//$cmd = "ps aux | grep auto_execute.sh |grep -v grep | wc -l";
			$cmd = "ps aux | grep start.sh |grep $device |grep -v grep | wc -l";
			$ret = remote_exec($cmd,$controller,$user,$pass);

			$check_start_process = rtrim($ret);

			if ($check_start_process != "0"){

				$content_arr = update_json_and_clean($username, $target_file, $device);
				$result_arr["content"] = $content_arr;
				$result_arr["msg"] = 'Device is in use, found start.sh running';
				echo json_encode($result_arr);
                		exit(0);
			}else{
				// remove old err.txt file on test machine

				$err_file = $home . "/" . $device . "_err.txt";

				$cmd = "rm -f $err_file";

				$ret = remote_exec($cmd,$controller,$user,$pass);

				//remove old err.txt file on web server
				$temp_err_file = "../../tempdata/log_data/" . $device . "_err.txt";
				$cmd = "rm -f $temp_err_file";
				$ret = exec($cmd);


				// remove old log
				$oldLog = $home . "/results_" . $device . "_" . $dev_runlist;

				$cmd = "rm -rf '$oldLog'*";

				$ret = remote_exec($cmd,$controller,$user,$pass);

				$cmd = "ls '$oldLog'*| wc -l";
				$ret = remote_exec($cmd,$controller,$user,$pass);
				$check_logs = rtrim($ret);

				if ($check_logs == "0"){
					// do remote execution

					$cmd = "cd $home; sh start.sh $device 'res/$dev_runlist' 2> $err_file";
					remote_exec($cmd,$controller,$user,$pass);
					
					$content_arr = update_json_and_clean($username, $target_file,$device);
					$result_arr["content"] = $content_arr;
					$result_arr["success"] = true;

					echo json_encode($result_arr);



				}else{
					//echo "<script>alert(\"Not able to move old logs $controller. Please check permission of $oldLog\");</script>";
					$content_arr = update_json_and_clean($username, $target_file, $device);
					$result_arr["content"] = $content_arr;
					$result_arr["msg"] = 'Remove existing log dir failed';
					echo json_encode($result_arr);
					exit(0);
				}
			}
		}
	}
} else {
	echo json_encode(array('msg'=>'No server name is provided'));
}

?>
