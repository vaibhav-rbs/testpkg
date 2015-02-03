<?php
include 'get_clients.php';

$server = $_POST['name'];
$clients = get_clients();

foreach($clients->clients as $machine) {
	if($machine->name == $server) {
		$ip = $machine->ip;
		$user = $machine->user;
		$pwd = $machine->pwd;
		break;
	}
}

// connect to remote server
$conn = ssh2_connect($ip, 22);

if($conn) {
	if(ssh2_auth_password($conn, $user, $pwd)) {
		// get the current system of remote test machine
		$stream = ssh2_exec($conn, "date +'%m-%d-%Y %T'");
		stream_set_blocking($stream, true);
		echo fgets($stream);		
	} else {
		die('Authentication failed');
	}
} else {
	die('Connection failed');
}
?>