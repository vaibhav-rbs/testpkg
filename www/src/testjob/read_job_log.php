<?php
require_once 'SOAP/Client.php';
include '../db/api.php';
include 'get_clients.php';

$log_path = $_POST['logpath'];
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

// connect to remote server
$conn = ssh2_connect($ip, 22);

if($conn) {
	if(ssh2_auth_password($conn, $user, $pwd)) {
		if(is_file_exist($log_path)) {
			$content = read_content($log_path);
			
			echo json_encode($content);
		} else {
			echo "Log file does not exist.";
		}
	} else {
		die('Authentication failed');
	}
} else {
	die('Connection failed');
}

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
		$line = trim($line);
		
		if (strlen($line) > 0) {
			$eachline["log"] = $line;
			array_push($lines, $eachline);
		}	
		/*f(preg_match("/(\[.*?\])/", $line, $matches)) {
			$eachline["time_stamp"] = $matches[0];
			$remove = array($matches[0], "<", ">");
			$line = trim(str_replace($remove, "", $line));
			$eachline["message"] = $line;
		}*/
	}
	
	fclose($stream);
	return $lines;
}
?>