<?php


$detail=$_REQUEST['detail'];
$more_detail=$_REQUEST['more_detail'];

$dArray = $detail["Table"];
$count = count($dArray);


$mArray = $more_detail["Table"];
$m_count = count($mArray);



//Sorting
$sort_key = array();
for( $i = 0; $i < $count; $i++) array_push($sort_key, $dArray[$i]["testCaseName"]);
array_multisort($sort_key, SORT_ASC, $dArray);





// result = N if the test is not run
for ($i = 0 ; $i < $count ; $i++){
	if (($dArray[$i]["testResult"] != "P") && ($dArray[$i]["testResult"] != "F") && ($dArray[$i]["testResult"] != "B") && ($dArray[$i]["testResult"] != "I"))
		$dArray[$i]["testResult"] = "N";

}


// Find the latest result which is uploaded to TC for each test cases from mArray()


$test_index = array();
for ($i = 0; $i < $count ; $i++){
        for ($j = 0; $j < $m_count; $j++) {
                if ($dArray[$i]["testCaseName"] == $mArray[$j]["testCaseName"]){
                        $test_index[$i]["testCaseName"] = $dArray[$i]["testCaseName"];
                        $test_index[$i]["index"] = $j;
			$test_index[$i]["runDate"] = $mArray[$j]["runDate"];
                }
        }
}



$db_server = "localhost";
$db_user = "root";
$db_pass = "root123";
$db_name = "invaderPlusDb";


$block_string = "<p><h2>Detail results</h2></p>";
$dbObject = new mysqli($db_server, $db_user, $db_pass, $db_name);
for($i=0; $i < $count ; $i++){

	$test_plan_name = $dArray[$i]["testPlanName"];
	$test_case_name = $dArray[$i]["testCaseName"];
	if ($dArray[$i]["testResult"] == "P") $value = "[PASS]";
	if ($dArray[$i]["testResult"] == "F") $value = "[FAIL]";
	if ($dArray[$i]["testResult"] == "B") $value = "[BLOCK]";
	if ($dArray[$i]["testResult"] == "I") $value = "[INDETERMINATED]";
	if ($dArray[$i]["testResult"] == "N") $value = "[NOT RUN]";


	$block_string = $block_string . "<p><a name=\"" . $test_case_name . "\"></a></p>";
	$block_string = $block_string . "<p><h3><FONT COLOR=\"Blue\">" . $test_case_name . ":   " . $value . "</FONT></h3></p>";
	$block_string = $block_string . "<p><h3>" . "Description:" . "</h3>" . $dArray[$i]["caseDescription"] . "</p>";


	$run_time = convert_time_format($test_index[$i]["runDate"]);




	// Get info from content server DB
	if(!$my_result = $dbObject->query("select Result.id, run_timestamp, corid, block_reason, exec_time from Execution, Result where Result.execution_id=Execution.id and Execution.test_plan_name = '$test_plan_name' and Result.test_case_name = '$test_case_name' and Execution.run_timestamp = '$run_time'")) echo "Select failed: (" . $dbObject->errno . ") " . $dbObject->error;
	$row = $my_result->fetch_assoc();


	if($row){

		// get block reason from DB
		$breason = $row['block_reason'];
		$exectime = $row['exec_time'];
		if ($breason){
			$block_string = $block_string . "<p><h3>" . "Block Reason:" . "</h3>" . "EXCEPTION occurred while performing " . $breason . "</p>";
		}
		$block_string = $block_string . "<p><h3>" . "Execution Time:" . "</h3>" . $exectime . "</p>";

	
		//construct runlist name
		$corid = $row['corid'];
		$run_time = $row['run_timestamp'];

		//construct time stamp in result directory name
		list($date, $time) = split(" ", $run_time);


		$a1 = array();
		$a2 = array();

		$a1 = split("-", $date);
		$a2 = split(":", $time);
		$time_str = $a1[0] . "_" . $a1[1] . "_" . $a1[2] . "_" . $a2[0] . "_" . $a2[1] . "_" . $a2[2];

		$sub_string = $corid . "_" . $test_plan_name . ".xml_" . $time_str;

		$flag =0;	
		$dirname = "/datafiles/logfiles/logs";
		if (is_dir($dirname)) {
    			if ($dh = opendir($dirname)) {
				while (($filename = readdir($dh)) !== false) {
					$pos = strpos($filename,$sub_string);

					if($pos === false) {
					}else {
						$result_dir = $filename;
						$flag = 1;
						break;	
 
					}
        			}
                	}
        		closedir($dh);
		}

		if ($flag == 1){

			$result_id = $row['id'];
	
			if(!$my_result2 = $dbObject->query("select device_serial_num, dev_type, task,step_result,dev_log_name, test_log_name, anr_log_name from Step where result_id='$result_id'")) echo "Select failed: (" . $dbObject->errno . ") " . $dbObject->error;


			$table_string = "<table id=" . $test_case_name . " style=\"padding:5px;\" class=\"stylesample\" border=\"1\" width=\"80%\"><tr><th width=\"18%\">Device ID</th><th width=\"12%\">Device Type</th><th width=\"12%\">Task</th><th width=\"6%\">Result</th><th width=\"18%\">Test Log</th><th width=\"18%\">Device Log</th><th width=\"18%\">Screen Capture</th></tr>";
			while($row2 = $my_result2->fetch_assoc()) {

				// copy testlog and devlog under tempdata/log_data/<corid>/

				$log_dir = "../../tempdata/log_data";
				if(!is_dir($log_dir)){
					$make_log_dir = "mkdir $log_dir";
					exec($make_log_dir);
				}
				

				$user_dir = "../../tempdata/log_data/" . $corid;
				if(!is_dir($user_dir)){
					$make_user_dir = "mkdir $user_dir";
					exec($make_user_dir);
				}
				
				$logfile = "/datafiles/logfiles/logs/" . $result_dir . "/" . $row2["test_log_name"];
				$dest1 = $user_dir . "/" . $row2["test_log_name"];
				$command = "cp '" . $logfile . "' " . "'" . $dest1 . "'";
				exec($command);

				$logfile = "/datafiles/logfiles/logs/" . $result_dir . "/" . $row2["dev_log_name"];
				$dest2 = $user_dir . "/" . $row2["dev_log_name"];
				$command = "cp '" . $logfile . "' " . "'" . $dest2 . "'";
				exec($command);

				$dest3 = "";
				if ($row2["anr_log_name"] != ""){
					$logfile = "/datafiles/logfiles/logs/" . $result_dir . "/" . $row2["anr_log_name"];
					$dest3 = $user_dir . "/" . $row2["anr_log_name"];
					$command = "cp '" . $logfile . "' " . "'" . $dest3 . "'";
					exec($command);
					$dest3 = "tempdata/log_data/" . $corid . "/" . $row2["anr_log_name"];
				}

				$dest1 = "tempdata/log_data/" . $corid . "/" . $row2["test_log_name"];
				$dest2 = "tempdata/log_data/" . $corid . "/" . $row2["dev_log_name"];

				if( $dest3 == "") {
					$table_string = $table_string . "<tr><td>" . $row2["device_serial_num"] . "</td><td>" . $row2["dev_type"] . "</td><td>" . $row2["task"] . "</td><td>" . $row2["step_result"] . "</td><td><a href=\"" . $dest1 . "\" onclick=\"return popup(this)\">View Content</a></td><td><a href=\"" . $dest2 . "\"i onclick=\"return popup(this)\">View Content</a></td><td></td></tr>";
				}else{
					$table_string = $table_string . "<tr><td>" . $row2["device_serial_num"] . "</td><td>" . $row2["dev_type"] . "</td><td>" . $row2["task"] . "</td><td>" . $row2["step_result"] . "</td><td><a href=\"" . $dest1 . "\" onclick=\"return popup(this)\">View Content</a></td><td><a href=\"" . $dest2 . "\"i onclick=\"return popup(this)\">View Content</a></td><td><a href=\"" . $dest3 . "\" onclick=\"return popup(this)\">View Content</a></td></tr>";


				}

			}
			$table_string = $table_string . "</table>";
			$block_string = $block_string . $table_string;

		}
	}
	$block_string = $block_string . "<p><a href=\"#original\">Go Back</a></p>";

}




	
$rArray = array();
$rArray["display"] = $block_string;

echo json_encode($rArray);

function convert_time_format($in){
	list($dstring, $tstr) = split("T", $in);
	list($tstring, $left) = split("\+", $tstr);

	$out = $dstring . " " . $tstring;
	return $out;
}


?>
