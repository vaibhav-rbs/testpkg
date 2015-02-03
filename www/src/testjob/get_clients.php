<?php
require_once 'SOAP/Client.php';

get_clients();

function get_clients() {
	$result = "";
	$file = '/datafiles/clients.json';
	$fp = fopen($file, 'r');
	
	if($fp) {
		$result = fread($fp, filesize($file));
		fclose($fp);
	}
	
	return json_decode($result);
}
?>