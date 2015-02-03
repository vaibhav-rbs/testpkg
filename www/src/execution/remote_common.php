<?php


function remote_exec($cmd,$ip,$user,$pass) {
	if (!function_exists("ssh2_connect")) die("function ssh2_connect doesn't exist");

	if(!($con = ssh2_connect($ip, 22))){
    		return "fail: unable to establish connection\n";
	} else {
    		// try to authenticate with username root, password secretpassword
    		if(!ssh2_auth_password($con, $user, $pass)) {
        		return "fail: unable to authenticate\n";
    		} else {
        		// execute a command
        		if (!($stream = ssh2_exec($con, $cmd ))) {
            			return "fail: unable to execute command\n";
        		} else {
            			// collect returning data from command
            			stream_set_blocking($stream, true);
            			$data = "";
            			while ($buf = fread($stream,4096)) {
                			$data .= $buf;
				}
            		}
            		fclose($stream);
	    		return $data;
        	}
    	}
}




?>
