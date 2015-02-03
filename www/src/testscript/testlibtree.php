<?php
/*
 * testlibtree.php
 * Jung Soo Kim
 * April 10, 12
 * This is server side code to handle with data query from InvaderPlusDb database
 */
require_once 'SOAP/Client.php';
include 'testlibDB.php';

$framework = $_REQUEST['framework'];

// on initialization, load frameworks or get the selected node id
$nodeId = isset($_POST['id']) ? trim($_POST['id']) : printPackages($framework);

// if package node is clicked, populate methods
if ($nodeId > 0) {
	printMethods($nodeId);
}

function printPackages($framework) {
	echo json_encode(getPackages($framework));
}

function printMethods($package) {
	echo json_encode(getMethods($package));
}
?>