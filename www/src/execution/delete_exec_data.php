<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('convert_date_tc.php');

$db_server = "localhost";
$db_user = "root";
$db_pass = "root123";
$db_name = "invaderPlusDb";




$execlist = $_REQUEST['execlist'];
$execlist = chop($execlist);
//$execlist = "results_0A3BC2B50B020014_0A3BC2B50B020014_crmg76_demo_product - (MD Advance Platforms) Cycle 1.xml_2012_07_10_14_47_31";


// parse execution list's name to get all info

$tmp_arr = array();
$tmp_arr = split("_" , $execlist);
$user_name = $tmp_arr[3];
$d_serial_num = $tmp_arr[1];
$sep = "_" . $user_name . "_";
list($first, $second) = split($sep, $execlist);
list($plan, $runtime_str) = split(".xml_", $second);



$arr_tmp2 = array();
$arr_tmp2 = split("_", $runtime_str);
$runtime_org = "$arr_tmp2[0]-$arr_tmp2[1]-$arr_tmp2[2] $arr_tmp2[3]:$arr_tmp2[4]:$arr_tmp2[5]";
$runtime = convert_date_format($runtime_org);




// clean this execution from Execution table
$dbObject = new mysqli($db_server, $db_user, $db_pass, $db_name);
if ($dbObject->connect_errno){
	//echo "Failed to connect to MySQL: (" . $dbObject->connect_errno . ") " . $dbObject->connect_error;
	echo json_encode(array('msg'=>'Not able to connect to mysql DB'));
	exit(0);
}

if(!$my_result = $dbObject->query("select id from Execution where corid = '$user_name' and device_serial_num = '$d_serial_num' and run_timestamp = '$runtime_org'")){
	 //echo "Select failed: (" . $dbObject->errno . ") " . $dbObject->error;
	 echo json_encode(array('msg'=>'Query mysql DB from Execution table failed'));
	 exit(0);
}else{

	 $row = $my_result->fetch_assoc();
         $my_value = $row['id'];
	 $my_result->free();
	 //echo "Execution ID = " . $my_value . "\n";


	 if(!$my_result2 = $dbObject->query("select id from Result where execution_id = '$my_value'")){

	 	echo json_encode(array('msg'=>'Query mysql DB from Result table failed'));
	 	exit(0);
	 }else{
		while ($row2 = $my_result2->fetch_assoc()) {
			$result_id = $row2["id"];
			if(!$my_result3 = $dbObject->query("select id from Step where result_id = '$result_id'")){
	 			echo json_encode(array('msg'=>'Query mysql DB from Step table failed'));
	 			exit(0);
			}else{
         			if(!$dbObject->query("delete from Result where id = '$result_id'")){
	 				echo json_encode(array('msg'=>'Delete from Result table failed'));
	 				exit(0);
	 			}
				while($row3 = $my_result3->fetch_assoc()) {
					$step_id = $row3["id"];
         				if(!$dbObject->query("delete from Step where id = '$step_id'")){
	 					echo json_encode(array('msg'=>'Delete from Step table failed'));
	 					exit(0);
	 				}
				}
				$my_result3->free();
			}
    		}
		$my_result2->free();
          }

          if(!$dbObject->query("delete from Execution where id = '$my_value'")){
	 	echo json_encode(array('msg'=>'Delete from Execution table failed'));
	 	exit(0);
	  }
}
$dbObject->close();



// Remove temp log files for this user from tempdata/log_data/<user_name>

$cmd_remove_tmp_log = "rm -rf ../../tempdata/log_data/" . $user_name;
exec($cmd_remove_tmp_log);


// remove this entry from tc_ready/$user_name file and tc_ready file after delete this execution
// take care tc_ready/<corid>
$tc_txt = "/datafiles/logfiles/tc_ready/" . $user_name;
$tmp_txt = "/tmp/tc_ready_" . $user_name;
$infile=fopen($tc_txt,"r");
$outfile = fopen($tmp_txt, 'w');
while($line = fgets($infile)){
	$line = chop($line);
        if($line != $execlist)
		fwrite($outfile, $line . "\n");
}
fclose($infile);
fclose($outfile);

$cmd_back_tc_ready = "cp $tmp_txt $tc_txt";
exec($cmd_back_tc_ready);
chmod($tc_txt, 0777);
$cmd_clean = "rm $tmp_txt";
exec($cmd_clean);

// Remove result dir from /datafiles/logfiles/logs
$result_dir = '/datafiles/logfiles/logs/' . $execlist;
if (file_exists($result_dir)){
	$cmd = "rm -rf '$result_dir'";
	exec($cmd);
	if (file_exists($result_dir)){
	 	echo json_encode(array('msg'=>'Failed to remove result dir on CS'));
	 	exit(0);

	}


}else{
	 echo json_encode(array('msg'=>'Found no result dir on CS'));
	 exit(0);

}

echo json_encode(array('msg'=>"success"));

?>
