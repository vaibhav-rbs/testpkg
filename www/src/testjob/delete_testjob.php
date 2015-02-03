<?php
require_once 'SOAP/Client.php';
include '../db/api.php';
include 'get_clients.php';

$jobId = $_POST['id'];
$testMachine = $_POST['testMachine'];
$testJob = $_POST['testJob'];
$device = $_POST['device'];

$clients = get_clients();

foreach($clients->clients as $machine) {
	if($machine->name == $testMachine) {
		$ip = $machine->ip;
		$user = $machine->user;
		$pwd = $machine->pwd;
		$repo = $machine->repo;
		break;
	}
}

// connect to remote server
$conn = ssh2_connect($ip, 22);

if($conn) {
	if(ssh2_auth_password($conn, $user, $pwd)) {
		// delete crontab
		$dest = $repo . "tests/common-baseline/tools/ATFLite";
		$cmd = "cd $dest;python atflite.py -d \"$device" . "_$testJob\"";
		$stream = ssh2_exec($conn, $cmd);
		$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		stream_set_blocking($errorStream, true);
		stream_set_blocking($stream, true);
		
		$errorMsg = stream_get_contents($errorStream);
		
		// if error found, report it.
		if (strlen($errorMsg) > 0) {
			die ("Failed in deleting test job:\n$errorMsg");
		}
		
		// update database by deleting the test job record
		if (delete_testjob($jobId)) {
			echo 1;
		} else {
			die ('Deleting test job in database is failed.');
		}
	} else {
		die('Authentication failed');
	}
} else {
	die('Connection failed');
}	
?>