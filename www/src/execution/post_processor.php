<?php


class post_processor{

	
  function post_processor(){}

   // This function take the name of result directory and return all the information about this execution. Include an array of arrays that each contain information of one test
   public function get_log_array ($result_dir_name){

	$arr_tmp = array();
	$arr_tmp = split("_", $result_dir_name);

	$sep = $arr_tmp[3] . "_";
        list($ignore,$wanted) = split($sep, $result_dir_name); // want string after corid that contains plan name and time info
	list($tname,$time_info) = split(".xml_", $wanted);

        $arr_tmp2 = array();
        $arr_tmp2 = split("_", $time_info);

	$test_all_array = array();

	$result_array = array("test_plan_name" => "default", "device_build_num" => "default", "device_serial_num" => "default", "device_hardware_ver" => "default", "run_timestamp" => "","corid" => "", "test_array" => $test_all_array);
	$device_num = $arr_tmp[1];
	$result_array["device_serial_num"] = $arr_tmp[1];
	$result_array["corid"] = $arr_tmp[3];

	$result_array["test_plan_name"] = $tname;

	$result_array["run_timestamp"] = "$arr_tmp2[0]-$arr_tmp2[1]-$arr_tmp2[2] $arr_tmp2[3]:$arr_tmp2[4]:$arr_tmp2[5]";


	$result_log = "/datafiles/logfiles/logs/\"$result_dir_name\"/0000.driver.log";

	$runlist = "/datafiles/logfiles/logs/'$result_dir_name'/runlist.xml";
	$myrunlist = "/tmp/" . $device_num . "_runlist.xml";
	// Read myrunlist to build ID array

	$cmd = "cp $runlist $myrunlist";
        exec($cmd);

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
				$str = $this->convert_id($ID);
				array_push($ID_array, $str);

			}
			$ID++;
		} 


	}

	$id_count = count($ID_array);
	for($i = 0; $i < $id_count ; $i++){
		$runID = $this->get_runID($ID_array[$i]);
		$cmd = "grep -A 1 \"$runID\" $result_log| grep Creating | head -n 1";
		$ret = exec($cmd);
		list($a1, $b1) = split("/logs/", $ret);
                $length = strlen($b1) - 26;
                $t_name = substr($b1, 0, $length);
                $result_array["test_array"][$i]["test_case_name"] = $t_name;
		$b1 = chop($b1);
               	$result_array["test_array"][$i]["test_log_name"] = $b1;







		
		//get start time
		$time_arr = array();
		$time_arr = split(" ", $ret);
		$start_time = $time_arr[1];
		$start_sec = $this->hoursToSeconds($start_time);

		$search_data = "Module " . $ID_array[$i] . ".";

		$cmd = "grep '$search_data' $result_log | grep 'resulted in'";
		$ret = exec($cmd);
               	list($a2 , $b2 ) = split("resulted in ", $ret);
               	list($c2 , $d2 ) = split("\.", $b2);

		//get end time
		$time_arr2 = array();
		$time_arr2 = split(" ", $ret);
		$end_time = $time_arr2[1];
		$end_sec = $this->hoursToSeconds($end_time); 
			
		if( $end_sec >= $start_sec )
			$exec_time = $end_sec - $start_sec;	
		else
			$exec_time = 86400 - $start_sec + $end_sec;


               	list($a3 , $b3 ) = split("\.LogicalMethods\.", $ret);
		list($taskname, $junk1) = split(" resulted in ", $b3);
		$dd_array = array();
		$dd_array = split(":", $a3);
		$ddd_array =array();
		$ddd_array = split("\.", $dd_array[4]);

		$d_array = split("\.", $time_arr2[5]);

		$result_array["test_array"][$i]["task"] = $taskname;
       		$result_array["test_array"][$i]["dev_log_name"] = $result_array["test_array"][$i]["test_case_name"] . "_" . $d_array[0] . ".log";


		//Check existing of anr log file under result dir to set up anr_log_name, otherwise null

		$a_name = $result_array["test_array"][$i]["test_case_name"] . "_" . $d_array[0] . ".png";
		$anr_log = "/datafiles/logfiles/logs/'$result_dir_name'/'$a_name'";


		$cmd2 = "ls $anr_log | wc -l";
		$ret2 = exec($cmd2);
		$ret2 = chop($ret2);
		if ($ret2 != 0) $result_array["test_array"][$i]["anr_log_name"] = $a_name;
                else $result_array["test_array"][$i]["anr_log_name"] = "";
		// end making anr log name
                
		$result_array["test_array"][$i]["device"] = $ddd_array[1];

		if($result_array["test_array"][$i]["device"] == $result_array["device_serial_num"]){
			$result_array["test_array"][$i]["device_type"] = "Target"; 
		}else{
			$result_array["test_array"][$i]["device_type"] = "Companion"; 

		}




                $result_array["test_array"][$i]["block_reason"] = "";
		if ($c2 == "[PASS]"){
			$result = "P";
	 	}else{
			if ($c2 == "[FAIL]"){
				$result = "F";
			}else{
				if ($c2 == "[EXCEPTION]"){
					$result = "B";
               				$result_array["test_array"][$i]["block_reason"] = $result_array["test_array"][$i]["task"] . " ";
				}else{
					$result = "I";
				}
			}
		}
               	$result_array["test_array"][$i]["test_result"] = $result;
               	$result_array["test_array"][$i]["group_type_1"] = "NA";
               	$result_array["test_array"][$i]["group_type_2"] = "NA";
               	$result_array["test_array"][$i]["exec_type"] = "Automated";
               	$result_array["test_array"][$i]["exec_time"] = $exec_time;

	}

	// save the json string for debug purpose
	//$fp3 = fopen('/tmp/jinglog.json', 'w');
        //fwrite($fp3, json_encode($result_array));
        //fclose($fp3);
	
	$cmd = "rm -f $myrunlist";
        exec($cmd);

	return $result_array;
   }


   // This function take result_array as input, check if single test case has multiple steps, it will summerize the result for one result per test case.
   // Any step has result=I will get final result=I, any step has result=F but no step has result=I will have final result=F, a test case with final result=P means all steps are passed. 

   public function get_log_array_for_TC($result_array){

	$orig_test_array = $result_array["test_array"];
        $num = count($orig_test_array);

        $new_test_array = array();
	$k = 0;
        for ($i=0; $i < $num ; $i++){

		$num_new = count($new_test_array);
		$flag =0;
		for ($j=0 ; $j < $num_new ; $j++) {
			if ($orig_test_array[$i]["test_case_name"] == $new_test_array[$j]["test_case_name"]){
				$flag =1;
				break;
			}
		}
		if ($flag == 0){
                	$new_test_array[$k]["test_case_name"] = $orig_test_array[$i]["test_case_name"];
                	$new_test_array[$k]["test_result"] = $orig_test_array[$i]["test_result"];
                	$new_test_array[$k]["group_type_1"] = $orig_test_array[$i]["group_type_1"];
                	$new_test_array[$k]["group_type_2"] = $orig_test_array[$i]["group_type_2"];
                	$new_test_array[$k]["exec_type"] = $orig_test_array[$i]["exec_type"];
                	$new_test_array[$k]["exec_time"] = $orig_test_array[$i]["exec_time"];
                	$new_test_array[$k]["block_reason"] = $orig_test_array[$i]["block_reason"];
			$k++;


		}

		if ($flag == 1){

			if ($orig_test_array[$i]["test_result"] == "I")
                        	$new_test_array[$j]["test_result"] = "I";
			
			if ($orig_test_array[$i]["test_result"] == "B" && $new_test_array[$j]["test_result"] != "I")
                        	$new_test_array[$j]["test_result"] = "B";
			
			
			if ($orig_test_array[$i]["test_result"] == "F" && $new_test_array[$j]["test_result"] == "P")
                        	$new_test_array[$j]["test_result"] = "F";
			
			$new_test_array[$j]["exec_time"] = $new_test_array[$j]["exec_time"] + $orig_test_array[$i]["exec_time"];
			
			if ($new_test_array[$j]["test_result"] == "B")
				$new_test_array[$j]["block_reason"] = $new_test_array[$j]["block_reason"] . $orig_test_array[$i]["block_reason"];
		}



        }

        $result_array["test_array"] = $new_test_array;

	// save the json string for debug purpose
	//$fp4 = fopen('/tmp/jinglog_new.json', 'w');
        //fwrite($fp4, json_encode($result_array));
        //fclose($fp4);


	return $result_array;

   }


   public function archive_to_test_central($result_array){



   }


   // take the array of the result of one execution, then archive to DB of content server. Execution table and Result table are updated
   public function archive_to_content_server($result_array, $result_array0){
	$test_result_array = $result_array["test_array"];
	$test_result_array0 = $result_array0["test_array"];

	$db_server = "localhost";
	$db_user = "root";
	$db_pass = "root123";
	$db_name = "invaderPlusDb";



	$test_plan_name = $result_array["test_plan_name"];
	$device_build_num = $result_array["device_build_num"];
	$device_serial_num = $result_array["device_serial_num"];
	$device_hardware_ver = $result_array["device_hardware_ver"];
	$run_timestamp = $result_array["run_timestamp"];
	$corid = $result_array["corid"];




	$dbObject = new mysqli($db_server, $db_user, $db_pass, $db_name);
	if ($dbObject->connect_errno)
 		echo "Failed to connect to MySQL: (" . $dbObject->connect_errno . ") " . $dbObject->connect_error;
	if(!$dbObject->query("insert into Execution (test_plan_name,device_build_num,device_serial_num,device_hardware_ver,run_timestamp,corid) values('$test_plan_name','$device_build_num','$device_serial_num','$device_hardware_ver','$run_timestamp','$corid')"))
 		echo "Insertion failed: (" . $dbObject->errno . ") " . $dbObject->error;




	if(!$my_result = $dbObject->query("select id from Execution where corid = '$corid' and device_serial_num = '$device_serial_num' and run_timestamp = '$run_timestamp'")) echo "Select failed: (" . $dbObject->errno . ") " . $dbObject->error;

	$row = $my_result->fetch_assoc();
	$my_value = $row['id'];


	$tnum = count($test_result_array);

	for ($i=0; $i < $tnum ; $i++){

		$test_case_name = $test_result_array[$i]["test_case_name"];
		$test_result = $test_result_array[$i]["test_result"];
		$group_type_1 = $test_result_array[$i]["group_type_1"];
		$group_type_2 = $test_result_array[$i]["group_type_2"];
		$exec_type = $test_result_array[$i]["exec_type"];
		$block_reason = $test_result_array[$i]["block_reason"];

		// convert time format of execution time for DB
		$exec_seconds = $test_result_array[$i]["exec_time"];
	        $dt = new DateTime('@' . $exec_seconds, new DateTimeZone('UTC'));
                $exec_time = $dt->format('H') . ":" . $dt->format('i') . ":" . $dt->format('s');





		if(!$dbObject->query("insert into Result (execution_id,test_case_name,group_type_1,group_type_2,test_result,exec_type,exec_time,block_reason) values('$my_value','$test_case_name','$group_type_1','$group_type_2','$test_result','$exec_type','$exec_time','$block_reason')")) 
			echo "Insertion  to Result table failed: (" . $dbObject->errno . ") " . $dbObject->error;
	


	}


	$tnum0 = count($test_result_array0);

	for ($j=0; $j < $tnum0 ; $j++){
		$test_case_name0 = $test_result_array0[$j]["test_case_name"];

		if(!$my_result0 = $dbObject->query("select Result.id from Result, Execution where Execution.corid = '$corid' and Result.execution_id = Execution.id and Result.test_case_name = '$test_case_name0' and Execution.run_timestamp = '$run_timestamp'")) echo "Select Result id failed: (" . $dbObject->errno . ") " . $dbObject->error;

		$row0 = $my_result0->fetch_assoc();
		$my_value0 = $row0['id'];

		$device0 = $test_result_array0[$j]["device"];
		$device_type0 = $test_result_array0[$j]["device_type"];
		$task0 = $test_result_array0[$j]["task"];
		$test_result0 = $test_result_array0[$j]["test_result"];
		$dev_log_name0 = $test_result_array0[$j]["dev_log_name"];
		$test_log_name0 = $test_result_array0[$j]["test_log_name"];
		$anr_log_name0 = $test_result_array0[$j]["anr_log_name"];
			

		if(!$dbObject->query("insert into Step (result_id,device_serial_num,dev_type,task,step_result,dev_log_name, test_log_name, anr_log_name) values('$my_value0','$device0','$device_type0','$task0','$test_result0','$dev_log_name0','$test_log_name0','$anr_log_name0')")) 
			echo "Insertion to Step table failed: (" . $dbObject->errno . ") " . $dbObject->error;
	}

	if ($my_result != null)
			$my_result->free();
	//if ($my_result0 != null)
	//	$my_result0->free();
	mysqli_close($dbObject);


	list($first, $second) = split (" ", $run_timestamp);
	list($year, $mon, $day) = split("-", $first);
	list($hour, $min, $sec) = split(":", $second);

	$log_file_name = "results_" . $device_serial_num . "_" . $device_serial_num . "_" . $corid . "_" . $test_plan_name . ".xml_" . $year . "_" . $mon . "_" . $day . "_" . $hour . "_" . $min . "_" . $sec . "\n";

	// add log_file_name to tc_ready/<corid>
	$tc_txt = "/datafiles/logfiles/tc_ready/" . $corid;
	$fp = fopen($tc_txt, 'a');
	fwrite($fp, $log_file_name);
	fclose($fp);
        chmod($tc_txt, 0777);
	// add log_file_name to tc_ready/all
	//$tc_txt2 = "/datafiles/logfiles/tc_ready/all";
	//$fp = fopen($tc_txt2, 'a');
	//fwrite($fp, $log_file_name);
	//fclose($fp);


   }
   public function hoursToSeconds ($hour) { // $hour must be a string type: "HH:mm:ss"

    	$parse = array();
    	if (preg_match ('#^(?<hours>[\d]{2}):(?<mins>[\d]{2}):(?<secs>[\d]{2})$#',$hour,$parse)) 
         	return (int) $parse['hours'] * 3600 + (int) $parse['mins'] * 60 + (int) $parse['secs'];

   }
   public function convert_id($num){
	$repeat = 4 - strlen((string) $num);
	$str = "$num";
	for($i=0; $i < $repeat ; $i++){
		$str = "0" . $str;

	}
   	return $str;
   }

   public function get_runID($str){

        $ret_str = "";
	$flag =0;
        for ($i = 0 ; $i < 4 ; $i++){
                if ($str[$i] == "0" && $flag == 0) {
		}else{
			$ret_str = $ret_str . $str[$i];
			$flag = 1;
		}
        }
        $ret_str = "New runID: " . $ret_str;
        return $ret_str;


   }


}

?>
