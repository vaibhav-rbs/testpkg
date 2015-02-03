<?php

include('remote_common.php');

$server = $_REQUEST['tname'];
$username = $_REQUEST['username'];

//$server = "jing";
//$username = "crmg76";

$arr = array();  // store array of strings from split
$device_arr = array(); //store array of device serial numbers
$json_dev = array(); // json array to be returned

$string = file_get_contents("../../tempdata/testservers.json");
$json_a = json_decode($string, true);
$row_array = $json_a["rows"];
$num = count($row_array);





for ($i=0; $i < $num ; $i++){
	if($row_array[$i]["name"] == $server){
		$controller = $row_array[$i]["ip"]; 
		$home = $row_array[$i]["home"];
		break;
	}
}





$ping_value = exec("ping -c 2 $controller | grep  -w \"0% packet loss\" | wc -l");
if($ping_value != "1"){
       	echo json_encode(array('msg'=>'ERROR_PING'));
	exit(0);
}


$user = "autotest";
$pass = "autotest";
$cmd_str = "ls $home | grep Makefile | wc -l";
$check_home = remote_exec($cmd_str, $controller, $user, $pass);
$check_home = rtrim($check_home);

if ($check_home != "1"){
       	echo json_encode(array('msg'=>'ERROR_HOME'));
	exit(0);
}


$cmd_str = "cd $home/SupportPackages/env; sh check_avail.sh";
$avail_info = remote_exec($cmd_str, $controller, $user, $pass);
$avail_info = rtrim($avail_info);

$pos = strpos($avail_info,"attached");

if($pos === false) {
       	echo json_encode(array('msg'=>'ERROR_ADB'));
	exit(0);
}


list($first, $second) = split ("attached", $avail_info);
$pos = strpos($second,"device");

if($pos === false) {
       	echo json_encode(array('msg'=>'ERROR_DEVICE'));
	exit(0);
}

$arr = split(" ", $second);
$a_count = count($arr);

for ($i=1; $i < $a_count ; $i++){

	array_push($device_arr, $arr[$i]);
	$i++;
}



$d_count = count($device_arr);
$k = 0;
for ($j=0; $j < $d_count ; $j++){

	$cmd_str = "ps aux | grep start.sh | grep $device_arr[$j] | grep -v grep";
	$ret_info = remote_exec($cmd_str, $controller, $user, $pass);
	$ret_info = rtrim($ret_info);
	$pos = strpos($ret_info,"start.sh");
	if($pos === false) {
		
		$cmd = "grep $device_arr[$j] ../../tempdata/*.comp | wc -l";
		$ret_count = exec($cmd);
		$ret_count = chop($ret_count);
		$cmd2 = "grep $device_arr[$j] ../../tempdata/* | grep .comp";
		$ret_str = exec($cmd2);
		list($a1, $b1) = split("\.comp:", $ret_str);
		list($c1, $ret_dev) = split("/tempdata/",$a1);


		if($ret_count > 0){
       			$new_row = array("name"=>"$server","device"=>"$device_arr[$j]","status"=>"In Use","runlist"=>"Companion device of $ret_dev");
			$json_dev["rows"][$k]= $new_row;
			$k++;
		}else{
       			$new_row = array("name"=>"$server","device"=>"$device_arr[$j]","status"=>"Ready To Be Used","runlist"=>"");
			$json_dev["rows"][$k]= $new_row;
			$k++;
		}
        	
	}else{

		list($first, $second) = split ("res/", $ret_info);
		list($fname_plus, $left) = split (".xml", $second);
		$sep = $device_arr[$j] . "_";
		list($nothing, $fname) = split ($sep, $fname_plus);
       		$new_row = array("name"=>"$server","device"=>"$device_arr[$j]","status"=>"In Use","runlist"=>"$fname");
		$json_dev["rows"][$k]= $new_row;
		$k++;
	}



}

$json_dev["total"]=$k;


// Save json data to <username>_device.json file
$file_name = "../../tempdata/" . $username . "_device.json";
$fp = fopen($file_name, 'w');
fwrite($fp, json_encode($json_dev));
fclose($fp);

//If a device is in use, check if there is a companion related with it, and mark it in use

$string2 = file_get_contents($file_name);
$json_a2 = array();
$json_a2 = json_decode($string2, true);
$row_array2 = $json_a2["rows"];
$num2 = count($row_array2);
for($i = 0; $i < $num2 ; $i++){
	if ($row_array2[$i]["status"] == "In Use"){
        	$target =$row_array2[$i]["device"];
		$file2 = "../../tempdata/" . $target . ".comp";
		if(file_exists($file2)){
			$cmd = "cat " . $file2;
			$companion = exec($cmd);
			$companion = chop($companion);
			for ($j = 0; $j < $num2 ; $j++){
				if ( $row_array2[$j]["device"] == $companion){
					$row_array2[$j]["status"] = "In Use";
					break;
				}

			}

		}
        }

}


//If a device is ready to be used, clean target.comp file

for($i = 0; $i < $num2 ; $i++){
        if ($row_array2[$i]["status"] == "Ready To Be Used"){
                $target =$row_array2[$i]["device"];
                $file2 = "../../tempdata/" . $target . ".comp";
                if(file_exists($file2)){
                        $cmd = "rm -f " . $file2;
                        exec($cmd);
                }
        }

}




$fp = fopen($file_name, 'w');
fwrite($fp, json_encode($json_a2));
fclose($fp);


echo json_encode($json_a2);


?>
