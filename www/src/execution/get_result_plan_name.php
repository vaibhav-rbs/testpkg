<?php


$user_name = $_REQUEST['user_name'];
//$user_name = "crmg76";
$fname_array = null;
$dir = "/datafiles/logfiles/tc_ready/";
$fname_array[0]["id"] = "--Select an execution from list--";
$fname_array[0]["text"] = "--Select an execution from list--";
$fname_array[0]["selected"] = true;
$user_file = "/datafiles/logfiles/tc_ready/" . $user_name;
$user_tmp_file = "/tmp/tmp_" . $user_name;
$ret = exec("rm -rf $user_tmp_file");
$ret = exec("sort $user_file > $user_tmp_file");


$j = 1;


if (is_dir($dir) && is_file($user_tmp_file)) {
		$this_file = fopen($user_tmp_file, "r");
		while($line = fgets($this_file)){

			$arr_tmp = array();
			$arr_tmp = split("_", $line);

			$sep = $arr_tmp[3] . "_";
        		list($ignore,$wanted) = split($sep, $line); // want string after corid that contains plan name and time info
			list($tname,$time_info) = split(".xml_", $wanted);

        		$arr_tmp2 = array();
        		$arr_tmp2 = split("_", $time_info);



			$device_serial_num = $arr_tmp[1];
			$test_plan_name = $tname;
			$run_timestamp = "$arr_tmp2[0]-$arr_tmp2[1]-$arr_tmp2[2] $arr_tmp2[3]:$arr_tmp2[4]:$arr_tmp2[5]";
			$corid = $arr_tmp[3];
			$show_string = $corid . "_" . $device_serial_num . "_" . $tname . "_" . $run_timestamp;

			$fname_array[$j]["id"] = $line;
			$fname_array[$j]["text"] = $show_string;
                        $j++;
		}
		fclose($this_file);


}




//print_r($fname_array);
if ($fname_array != null){

	echo json_encode($fname_array);
} else {
	echo json_encode(array('msg'=>'Found no result list files'));
}

?>
