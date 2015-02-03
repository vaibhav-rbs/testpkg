<?php
include('post_processor.php');
include('remote_common.php');



$server = $_REQUEST['name'];
$device = $_REQUEST['device'];
$runlist = $_REQUEST['runlist'];

//$server = "jing";
//$device = "0A3BC2B50E01B00C";
//$runlist = "crmg76_demo_product - (MD Advance Platforms) Cycle 2.xml";

$dev_runlist = $device . "_" . $runlist;

$user = "autotest";
$pass = "autotest";

list($core_user, $filename) = split ("_", $runlist);

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

// retrive name of directory of result
$target_dir = "results_" . $device . "_" . $dev_runlist;
$cmd = "cd $home;ls | grep '$target_dir'";
$ret = remote_exec($cmd,$controller,$user,$pass);
$result_dir = rtrim($ret);


if ($result_dir == ""){
	echo json_encode(array('msg'=>'No result is generated, please click Check Error Icon'));
       	exit(0);
}

// If result dir exist on content server, then exit
$local_dir = "/datafiles/logfiles/logs/$result_dir";
if (file_exists($local_dir)){
	echo json_encode(array('msg'=>'Auto archiving has already started. The operation may be in progress or completed. You well see the result on Archive page once archiving is completed.'));
	exit(0);
}

// mkdir /datafiles/logfiles/logs/results in content server/local
$remote_dir=$home . "/" . $result_dir . "/logs/";
$localcmd = "mkdir '$local_dir';chmod 777 '$local_dir'";
exec($localcmd);

// Copy all files under test server: results_XXX/logs to  content server: datafiles/logfiles/logs


$connection = ssh2_connect($controller, 22);
ssh2_auth_password($connection, $user, $pass);


$com ="ls '$remote_dir'";

$stream = ssh2_exec($connection, $com);
stream_set_blocking($stream,true);
//$cmd=fread($stream,4096);
$cmd = stream_get_contents($stream);

$arr=explode("\n",$cmd);
$total_files=sizeof($arr);

for($i=0;$i<$total_files;$i++){
	$file_name=trim($arr[$i]);
	if($file_name!=''){
		$remote_file=$remote_dir . "/" . $file_name;
		$local_file=$local_dir . "/" . $file_name;

                if(ssh2_scp_recv($connection, $remote_file,$local_file)){
                  //echo "File ".$file_name." was copied to $local_dir<br />";
                }
        }
}

fclose($stream);
$tmp_runlist = "/tmp/" . $dev_runlist;
$cmd = "cp '$tmp_runlist' '$local_dir/runlist.xml'";
$ret = exec($cmd);

$cmd = "rm -f '$tmp_runlist'";
exec($cmd);

	
$cmd = "chmod 777 -R '$local_dir'";
exec($cmd);

// Copy end

// Parse log then archive to content server
$return_array = array();

$processor = new post_processor();
$return_array0 = $processor -> get_log_array($result_dir); //create original array from log dir
$return_array = $processor -> get_log_array_for_TC($return_array0); // use original array to create a compact array to meet TC required
$processor -> archive_to_content_server($return_array,$return_array0); // compact array and original array are passed as 2 parameters
unset($processor);


echo json_encode(array('msg'=>"success"));

?>
