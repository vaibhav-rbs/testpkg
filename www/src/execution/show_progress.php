<?php

include('remote_common.php');


$server = $_REQUEST['name'];
$device = $_REQUEST['device'];
$username = $_REQUEST['username'];

$user = "autotest";
$pass = "autotest";
$rnum = rand();

$myrunlist = "/tmp/runlist$rnum";
$myresult = "/tmp/log$rnum";

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
                echo json_encode(array('msg'=>'companion'));
                exit(0);


	}



        $ping_value = exec("ping -c 2 $controller | grep  -w \"0% packet loss\" | wc -l");
        if($ping_value != "1"){
                echo json_encode(array('msg'=>'Not able to ping test server'));
                exit(0);
        }




	// Make sure there is an  existing runlist.xml on test server
	$check_runlist = 0;
        $device_username = $device . "_" . $username;
	$cmd = "ls $home/res| grep $device_username| wc -l";

	for ($i=0; $i < 500; $i++){
		$ret = remote_exec($cmd,$controller,$user,$pass);
		$check_runlist = rtrim($ret);

		if ($check_runlist == "1"){
			break;
		}
		sleep (2);

	}

	If ($check_runlist == "1"){

		// get runlist file name

		$cmd = "ls $home/res| grep $device_username";
		$ret = remote_exec($cmd,$controller,$user,$pass);
		$dev_runlist = rtrim($ret);

		// Remote receive runlist.xml
		$connection = ssh2_connect($controller, 22);
        	ssh2_auth_password($connection, $user, $pass);
        	$local = $myrunlist;
        	$remote = $home . "/res/$dev_runlist";
        	ssh2_scp_recv($connection, $remote, $local);
		

		// Read myrunlist to get all testid and set up data in json array

		$file=fopen($myrunlist,"r");
		if($file) $stream=fread($file, filesize($myrunlist));
		$xml = simplexml_load_string($stream);
		
		$test_result = $xml->xpath("//test");
		$num_test = count($test_result);
		$num_module_count_value_per_test = array();
		for ($i=0 ; $i < $num_test ; $i++){
			$b_array = $test_result[$i]->xpath("block");
			$num_block = count($b_array);
			$all_num = 0;
			for ($j=0 ; $j < $num_block ; $j++){
				$m_array = $b_array[$j]->xpath("module");
				$num_module = count($m_array);
				$all_num = $all_num + $num_module;

			}
			$attr = $test_result[$i]->attributes();
			$c_num = $attr['count'];

			$save_str = $all_num . ":" . $c_num;	
			array_push($num_module_count_value_per_test, $save_str);
		}

		//build up an array to contain Run ID that will be matched to each module to find step result
		$ID_array = array();
		$ID = 1;
		for($i=0; $i < $num_test ; $i++) {
			$save_value = $num_module_count_value_per_test[$i];
			list($m,$c) = split(":", $save_value);
			for ($j =0 ; $j < $c ; $j++){
				for($k = 0; $k < $m ; $k++){
					$ID++;
					$str = convert_id($ID);
					array_push($ID_array, $str);

				}
				$ID++;
			} 


		}

		
		$mjson_a = array();
		$r = 0;
		for ($i=0 ; $i < $num_test ; $i++){
			//find out number of block
        		$b_array = $test_result[$i]->xpath("block");
        		$num_block = count($b_array);
			//find out count of test
        		$attr = $test_result[$i]->attributes();
        		$c_num = $attr['count'];
			for ($cc = 0; $cc < $c_num ; $cc++){
           			for ($j=0 ; $j < $num_block ; $j++){
					// find out number of module
                			$m_array = $b_array[$j]->xpath("module");
                			$num_module = count($m_array);
					for ($k=0 ; $k < $num_module ; $k++){
						$attr = $m_array[$k]->attributes();
                        			$param = $m_array[$k]->xpath("param");
						$d_value = "$param[0]";
                        			$mjson_a["rows"][$r]["device"] = $d_value;
						$tt_value = $attr['id'];
						$t_value = "$tt_value[0]";
                        			$mjson_a["rows"][$r]["testid"] = $t_value;
						$mm_value = $attr['method'];
						$m_value = "$mm_value[0]";
						if($mjson_a["rows"][$r]["device"] == $device){
							$mjson_a["rows"][$r]["device_type"] = "Target";
						}else{
							$mjson_a["rows"][$r]["device_type"] = "Companion";
						}
                        			$mjson_a["rows"][$r]["desc"] = $m_value;
                        			$mjson_a["rows"][$r]["runid"] = $ID_array[$r];
                        			$mjson_a["rows"][$r]["result"] = "";
                        			$mjson_a["rows"][$r]["time"] = "";
						$r++;

					}


           			}

			}

		}

		$mjson_a["total"] = $r;
		exec("rm -f $myrunlist");

		// find remote results dir name


                $target_dir = "results_" . $device . "_" . $dev_runlist;
                $cmd = "cd $home;ls | grep '$target_dir'";
                $ret = remote_exec($cmd,$controller,$user,$pass);
                $result_dir = rtrim($ret);

                $result_log_dir = $home . "/" . $result_dir . "/logs/";


		$cmd = "ls '$result_log_dir'| wc -l";
		$ret = remote_exec($cmd,$controller,$user,$pass);
		$check_logs = rtrim($ret);

		if ($check_logs != "0"){

			$connection = ssh2_connect($controller, 22);
        		ssh2_auth_password($connection, $user, $pass);
        		$local = $myresult;
        		$remote = $result_log_dir . "0000.driver.log";
        		ssh2_scp_recv($connection, $remote, $local);


			for($i=0; $i < $r ; $i++){
				$cmd = "grep 'resulted in' " . $myresult . "|grep " . $mjson_a["rows"][$i]["runid"];
				$ret = exec($cmd);
				if ($ret != ""){
					$ret_array = split("\n", $ret);
					$data = split(" ", $ret_array[0]);
					list($left, $right) = split(" resulted in ", $ret_array[0]);
					$right = rtrim($right, ".");
                			$mjson_a["rows"][$i]["result"] = $right;
                			$mjson_a["rows"][$i]["time"] = $data[0] . " " . $data[1];
				}else{
					break;
				}
			}

			$cmd = "grep 'Test run complete' " . $myresult;
			$ret = exec($cmd);
			if ($ret != "") $mjson_a["total"] = 10000;
			exec("rm -f $myresult");
		}
		echo json_encode($mjson_a);
	}else{
		echo json_encode(array('msg'=>'Not able to find runlist.xml on test server'));
	
	}

} else {
	echo json_encode(array('msg'=>'Some errors occured.'));
}


function convert_id($num){
	$repeat = 4 - strlen((string) $num);
	$str = "$num";
	for($i=0; $i < $repeat ; $i++){
		$str = "0" . $str;

	}
	return $str;
}

?>
