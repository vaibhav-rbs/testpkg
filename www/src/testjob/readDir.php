<?php
require_once 'SOAP/Client.php';
include 'get_clients.php';

$machine_name = $_POST['machine'];

// get the authentication info to connect remote machine
$clients = get_clients();
$filelist = array();
$result = array();

foreach($clients->clients as $machine) {
	if($machine->name == $machine_name) {
		$ip = $machine->ip;
		$user = $machine->user;
		$pwd = $machine->pwd;
		$repo = $machine->repo;
		break;
	}
}

// get path
$path = isset($_POST['path']) ? $_POST['path'] : $repo;

// remove trailing slashes
$path = rtrim($path, '/');

// connect to remote server
$conn = ssh2_connect($ip, 22);

if($conn) {
	if(ssh2_auth_password($conn, $user, $pwd)) {	
		// read the directory
		$sftp = ssh2_sftp($conn);
		
		if(!$sftp) {
			die("Unable to startup SFTP subsystem: Unable to request SFTP subsystem");
		} else {
			if ($handle = opendir("ssh2.sftp://$sftp/$path")) {
				while ($entry = readdir($handle)) {
					// get file info
					$statInfo = ssh2_sftp_stat($sftp, "$path/$entry");
					
					// interpret the type of file
					if ($statInfo['mode'] & 0100000) {
						// this is a file
						$filelist[] = array('name' => $entry, 'size' => $statInfo['size'], 'type' => 'file');	
					}
					
					if ($statInfo['mode'] & 040000) {
						// this is a folder
						$filelist[] = array('name' => $entry, 'size' => '', 'type' => 'folder');
					}
				}
		
				sort($filelist);
				
				$result['pwd'] = $path;
				$result['filelist'] = $filelist;
				
				closedir($handle); // close dir
				
				echo json_encode($result);
			} else {
				die ("Cannot open the directory, $path\n");
			}	
		}
	} else {
		die('Authentication failed');
	}
} else {
	die('Connection failed');
}
?>