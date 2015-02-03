<?php

include('remote_common.php');


$server = $_REQUEST['name'];
$device = $_REQUEST['device'];
//$server = "Sunnyvale Main";
//$device = "0A3BC2B50E01B00C";

$user = "autotest";
$pass = "autotest";

$log_dir = "../../tempdata/log_data";

if(!is_dir($log_dir)){
	$make_dir = "mkdir ../../tempdata/log_data";
	exec($make_dir);
}



$myerror = "../../tempdata/log_data/" . $device . "_err.txt";
echo $myerror . "\n";
if(file_exists($myerror)){
	exec("rm $myerror");
}

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
                echo json_encode(array('msg'=>'Not able to ping test server'));
                exit(0);
        }




		
	$connection = ssh2_connect($controller, 22);
       	ssh2_auth_password($connection, $user, $pass);
       	$local = $myerror;
       	$remote = $home . "/" . $device . "_err.txt";
       	ssh2_scp_recv($connection, $remote, $local);
	echo json_encode(array('msg'=>'SUCCESS'));

} else {
	echo json_encode(array('msg'=>'Some errors occured.'));
}

?>
