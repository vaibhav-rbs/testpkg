<?php
require_once 'SOAP/Client.php';
include 'get_clients.php';

$machine = $_POST['machine'];
$data = $_POST['data'];
$userid = $_POST['userid'];
$clients = get_clients();

foreach($clients->clients as $key => $client) {
	if ($client->name == $machine) {
		$client->settings->$userid = $data;
		break;
	}
}

$clients->clients[$key] = $client;
file_put_contents('/datafiles/clients.json', json_encode($clients));
?>