<?php
include 'get_clients.php';

$server = $_POST['name'];
$userid = $_POST['userid'];
$clients = get_clients();

foreach($clients->clients as $machine) {
	if($machine->name == $server) {
		$ip = $machine->ip;
		$user = $machine->user;
		$pwd = $machine->pwd;
		$repo = $machine->repo;
		
		if (property_exists($machine, 'settings')) {		
			$settings = $machine->settings;
			
			// retrive settings by user id
			foreach($settings as $key => $value) {
				if ($key == $userid) {
					$userSetting = $value;
					break;
				}
			}
		}
		break;
	}
}

// if userSetting is empty, we have to set default key for custom script path
if (count($userSetting) == 0) {
	$userSetting[] = array('key' => 'Custom Script', 'value' => '');
}

// remove trailing slashes
$repo = rtrim($repo, '/');

// connect to remote server
$conn = ssh2_connect($ip, 22);

if($conn) {
	if(ssh2_auth_password($conn, $user, $pwd)) {
		$devices = get_devices($conn);

		$result = "";
		foreach($devices as $dev) {
			$device = array();
			$device["serial"] = $dev;
			
			$info = get_device_info($conn, $dev);
			foreach($info as $key => $value) {
				if(strpos($value, '=') !== FALSE) {
					$chucks = spliti("=", $value);
					$prop_name = str_replace("ro.product.", "", $chucks[0]);
					$prop_value = $chucks[1];
					
					if($prop_name == 'model' || $prop_name == 'display') {
						switch(strtolower($prop_value)) {
							case 'xt907':
								$device["image"] = '<img src="themes/icons/xt902_24.png">';
								break;
							case 'mb886':
								$device["image"] = '<img src="themes/icons/mb886_24.png">';
								break;
							case 'xt1053':
								$device["image"] = '<img src="themes/icons/xt1053_24.png">';
								break;
							case 'moto x':
								$device["image"] = '<img src="themes/icons/xt1053_24.png">';
								break;
							case 'droid razr':
								$device["image"] = '<img src="themes/icons/DROID_RAZR_24.png">';
								break;
							case 'xt1032':
								$device["image"] = '<img src="themes/icons/xt1032_24.png">';
								break;
							case 'moto g':
								$device["image"] = '<img src="themes/icons/xt1032_24.png">';
								break;
							default:
								$device["image"] = '<img src="themes/icons/nexus-5_24.png">';
								break;
						}
					}
					
					$device[$prop_name] = $prop_value;
				}	
			}
			
			$result[] = $device;
		}
		
		$output['devices'] = $result;
		$output['settings'] = $userSetting;

		echo json_encode($output);	
	} else {
		die('Authentication failed');
	}
} else {
	die('Connection failed');
}

/**
 * get_devices($conn)
 * @param unknown_type $conn
 */
function get_devices($conn) {
	$stream = ssh2_exec($conn, "adb devices");
	$errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
	stream_set_blocking($errorStream, true);
	stream_set_blocking($stream, true);
	
	$errorMsg = stream_get_contents($errorStream);
	
	// if error found, report it.
	if (strlen($errorMsg) > 0) {
		die ("Failed in detecting devices for the following reason\n$errorMsg");
	}
		
	$resp = "";
	while($line = fgets($stream)) {
		$line = trim(str_replace(array("\n", "\r"), '', $line));
		
		if(preg_match('/(\tdevice)$/', $line)) {
			$resp[] = preg_replace('/(\tdevice)$/', '', $line);
		}
	}
	
	return $resp;
}

function get_device_info($conn, $dev_serial) {
	$stream = ssh2_exec($conn, 'adb -s ' . $dev_serial . ' shell cat /system/build.prop | grep "ro.product"');
	stream_set_blocking($stream, true);
	
	$resp = "";
	while($line = fgets($stream)) {
		$line = trim(str_replace(array("\n", "\r"), '', $line));
		$resp[] = $line; 
	}
	
	return $resp;
}
?>