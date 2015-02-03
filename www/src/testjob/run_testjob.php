<?php
require_once 'SOAP/Client.php';
include('../db/api.php');
include 'get_clients.php';

$file = $_POST['file'];
$device = $_POST['serial'];
$sentby = $_POST['sentby'];
$client_name = $_POST['client'];

// get the repo property of client machine
$clients = get_clients();
foreach($clients->clients as $machine) {
	if($machine->name == $client_name) {
		$ip = $machine->ip;
		$user = $machine->user;
		$pwd = $machine->pwd;
		$repo = $machine->repo;
		$settings = $machine->settings->$sentby;
		break;
	}
}

$arr_file = read_content_file('/datafiles/testjob/' . $file);
$arr_file["repo"] = $repo;
$arr_file["serial"] = $device;
$arr_file["sentby"] = $sentby;

// initialize the settings
unset($arr_file["custom-script"]);
unset($arr_file["user-parameters"]);

foreach($settings as $conf) {
	if ($conf->key == "Custom Script") {
		$arr_file["custom-script"] = $conf->value;
	} else {
		$arr_file["user-parameters"][$conf->key] = $conf->value;
	}
}

write_content_file('/datafiles/testjob/' . $file, json_encode($arr_file));



if (isset($arr_file['start']) && isset($arr_file['every']) && isset($arr_file['repeats']) && 
    isset($arr_file['end']) && isset($arr_file['after'])) {
	    
    // connect to remote server
	$conn = ssh2_connect($ip, 22);
	
	if($conn) {
		if(ssh2_auth_password($conn, $user, $pwd)) {
			// save to queue (DB)		
			$result = add_testjob_db($device, $file, $arr_file, $sentby, $conn);
			
			if($result == 1) {
				// scp send the test job file to remote machine
				$dest = $repo . "tests/common-baseline/tools/ATFLite";
				ssh2_scp_send($conn, "/datafiles/testjob/$file", "$dest/$device" . "_" . $file, 0644);
				
				// start atflight
				$cmd = "cd $dest;python atflite.py \"$device" . "_" . $file . "\"";
				$stream = ssh2_exec($conn, $cmd);
				$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
				stream_set_blocking($errorStream, true);
				stream_set_blocking($stream, true);
				$errorMsg = stream_get_contents($errorStream);
				
				// if error found, report it.
				if (strlen($errorMsg) > 0) {
					die ("Failed in running $cmd for the following reason\n$errorMsg");
				} else {
					echo "Started the test job, '$file'.\nPlease click the device to see the status of test job.";	
				}
			} else {
				// if error occurrs during adding to database, display it
				echo $result;
			}
		} else {
			die('Authorization failed');
		}
	} else {
		die('Connection failed');
	}   	
} else {
	echo "Failed to start test job $file.\nPlease save the test job again and re-try.";
}


/**
 * read content from the file
 * @param $filename
 */
function read_content_file($filename) {
	$fp = fopen($filename, 'r');

	if ($fp) {
		$array = json_decode(fread($fp, filesize($filename)), true);
		fclose($fp);
	}
	
	return $array;
}

/**
 * write content to the file.
 * @param string $filename
 * @param json string $content
 */
function write_content_file($filename, $content) {
	$directory = dirname($filename);
	
	if(!file_exists($directory)) {
		mkdir($directory, 0777, true);
	}
	
	$fp = fopen($filename, 'w');
	fwrite($fp, $content);
	fclose($fp);	
}
?>