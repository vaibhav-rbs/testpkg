<?php

include('post_processor.php');
include('remote_common.php');



log_line("Agent is initiated==========================");

$file1 = "/datafiles/logfiles/need_archive_list.txt";
while(1){
	clearstatcache();
	if(file_exists($file1) && filesize($file1)!= 0){
		$fp = fopen($file1, "r+");
		if (flock($fp, LOCK_EX)) {  // acquire an exclusive lock
			$get_string = fread($fp, filesize($file1));
    			ftruncate($fp, 0);      // truncate file
    			fflush($fp);            // flush output before releasing the lock
    			flock($fp, LOCK_UN);    // release the lock
		}else{
			log_line("Couldn't get lock from agent");
		}
		fclose($fp);
		log_line($get_string);
		$archive_list_array = split("\n", $get_string);
		$aa_count = count($archive_list_array);
		log_line("Array count = " . $aa_count);
		log_line($file1 . " is emptyed");


		for ($i=0; $i < $aa_count; $i++){
			if ($archive_list_array[$i]){
				log_line($archive_list_array[$i] . " is started");

				$pid = pcntl_fork();
				if ($pid == -1) {
     					die('could not fork');
				} else if ($pid) {
     					// we are the parent
				} else {
     					// we are the child
					$rvalue = remote_monitor_and_archive($archive_list_array[$i]);
					log_line($archive_list_array[$i] . " is returned with " . $rvalue);
					exit();
        			}
			}
		}
	}
	sleep(60);
        echo "agent running\n";


}

//Monitor completion of execution on test machine, 2 mins later, then check result dir on content server, do archiving if no result dir found
function remote_monitor_and_archive($save_string){
	$save_arr = split(":", $save_string);
	log_line("home=" . $save_arr[0]);
	log_line("ip=" . $save_arr[1]);
	log_line("result dir=" . $save_arr[2]);
	$result_file = $save_arr[0] . "/" . $save_arr[2] . "/logs/0000.driver.log";
	$user = "autotest";
	$pass = "autotest";

	$flag2 = 0;
	for ($j=0 ; $j < 5 ; $j++){
		$cmd = "ls '$result_file' | wc -l";
		$ret = remote_exec($cmd, $save_arr[1], $user, $pass);
		$check_exist = rtrim($ret);
		if($check_exist == "1") {
			log_line("Found " . $result_file);
			$flag2 = 1;
			break;
		}
		sleep(60);
	}
	if($flag2 == 0) {
		log_line("Found no " . $result_file);
		return 0;
	}



	$flag = 0;
	for ($i=0 ; $i < 1000 ; $i++){
		
		$cmd = "grep 'Test run complete' '$result_file' | wc -l";
		$ret = remote_exec($cmd, $save_arr[1], $user, $pass);
		$check_complete = rtrim($ret);
		log_line($cmd . " and return= " . $ret);
		if($check_complete == "1") {
			log_line("Found complete for " . $result_file . "--" . $i);
			$flag = 1;
			break; //found execution complete
		}
		sleep(60);
	}
	if ($flag == 0){
		log_line("Cannot see completion of execution after 1000 mins (16.6 hrs) of monitoring for " . $result_file);
		return 0;
	}

	sleep(120); // After done of execution, wait for 2 mins, check the result dir on content server

	$dir1 = "/datafiles/logfiles/logs/" . $save_arr[2];
	if (!file_exists($dir1)){
		log_line($dir1 . " not exist on content server file system, archiving needed");
		$local_dir = "/datafiles/logfiles/logs/$save_arr[2]";
		$localcmd = "mkdir '$local_dir';chmod 777 '$local_dir'";
		exec($localcmd);
		
		$remote_dir=$save_arr[0] . "/" . $save_arr[2] . "/logs/";
		$connection = ssh2_connect($save_arr[1], 22);
		ssh2_auth_password($connection, $user, $pass);


		$com ="ls '$remote_dir'";

		$stream = ssh2_exec($connection, $com);
		stream_set_blocking($stream,true);
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
		$dev_runlist = get_dev_runlist($save_arr[2]);
		$tmp_runlist = "/tmp/" . $dev_runlist;
		$cmd = "cp '$tmp_runlist' '$local_dir/runlist.xml'";
		$ret = exec($cmd);

		$cmd = "rm -f '$tmp_runlist'";
		exec($cmd);
		log_line($cmd);


		// Parse log then archive to content DB
		$return_array = array();
		$processor = new post_processor();
		$return_array0 = $processor -> get_log_array($save_arr[2]); //create original array from log dir
		$return_array = $processor -> get_log_array_for_TC($return_array0); // use original array to create a compact array to meet TC required
		$processor -> archive_to_content_server($return_array,$return_array0); // compact array and original array are passed as 2 parameters
		unset($processor);
		log_line($dir1 . " not exist on content server file system, archiving is done");
	}else{
		log_line($dir1 . " exist on content server file system, so no archiving needed");

	}

	return 1;
}

function get_dev_runlist($dirname){

	list($wanted, $junk) = split(".xml", $dirname);
	$str1 = $wanted . ".xml";
	list($junk, $str2) = split("results_", $str1);
	list($head, $junk) = split("_", $str2);
	$del = "_" . $head . "_";
	list($head, $str3) = split($del, $str2);
	$ret_str = $head . "_" . $str3;
	return $ret_str;



}


function log_line($str){

	$dtime = date("m-d-y H:i:s");
	$target_str = $dtime . "::" . $str . "\n";
	$fp = fopen('/datafiles/logfiles/agent.log', 'a');
	fwrite($fp, $target_str);
	fclose($fp);
}

?>
