<?php
require_once 'SOAP/Client.php';
include '../db/api.php';
include 'get_clients.php';

$device = $_POST['device'];
$machine_name = $_POST['machine'];

// get the authentication info to connect remote machine
$clients = get_clients();

foreach($clients->clients as $machine) {
	if($machine->name == $machine_name) {
		$ip = $machine->ip;
		$user = $machine->user;
		$pwd = $machine->pwd;
		$repo = $machine->repo;
		break;
	}
}

$directory = $repo . "tests/common-baseline/tools/ATFLite/status";

// connect to remote server
$conn = ssh2_connect($ip, 22);
ssh2_auth_password($conn, $user, $pwd);

// get the test jobs in the queue
$jobs = get_testjobs($device);

// check the test job log file
// exists means it is running
// if exists, check the completion indicate inside the log
// it says so, it means the job is complete.
foreach($jobs as $index => $job) {
	$jobname = str_replace(".json", "", $job["test_job"]);
	$exectime = new DateTime($job["execution_time"]);
	
	$logfile = $device . "_" . $jobname . "_" . $exectime->format('YmdHi') . ".log";
	
	if(is_file_exist("$directory/$logfile")) {
		$job["status"] = "running";
		$job["log_path"] = "$directory/$logfile";
		$jobs[$index] = $job;
		
		// check for the completion
		$content = read_content("$directory/$logfile");
		$lastline = array_pop($content);
		
		if (strstr($lastline, 'Test job finished [SUCCESS]')) {
			array_shift($jobs);
			
			// delete from the database
			delete_testjob($job["job_id"]);
		} else if (strstr($lastline, 'Test job finished [ERROR]')) {
			$jobs[$index]["status"] = "ERROR";
		} else {
			$jobs[$index]["status"] = "Running";
		}
		
		//$second_lastline = array_pop($content);
		
		/*
		if(strstr($lastline, 'Test job finished')) {
			if(!strstr($second_lastline, 'ERROR:')) {
				array_shift($jobs);
			
				// delete from the database
				delete_testjob($job["job_id"]);	
			} else {
				$jobs[$index]["status"] = "ERROR";
			}
		}*/
	} 
} 

echo json_encode($jobs);

/**
 * is_file_exist: check if the file exists or not
 * return true if found, false if not
 * @param $conn
 * @param $file
 */
function is_file_exist($file) {
	global $conn;
	
	$file_exist = false;
	
	$stream = ssh2_exec($conn, "find \"$file\"");
	stream_set_blocking($stream, true);
	
	while($line = fgets($stream)) {
		// if the file exists, it shoule return the full path of the file
		if(strcmp(trim($line), $file) == 0) {
			$file_exist = true;
		}
	}
	
	fclose($stream);
	
	return $file_exist;
}

/**
 * read_content
 * @param string $file
 */
function read_content($file) {
	global $conn;
	$lines = array();
	
	$stream = ssh2_exec($conn, "cat \"$file\"");
	stream_set_blocking($stream, true);
	
	while($line = fgets($stream)) {
		array_push($lines, trim($line));
	}
	
	return $lines;
}
?>