<?php
include 'testlibDB.php';

$header = array();

$package = $_REQUEST['package'];

$methods = getMethods($package);

echo json_encode($methods);
?>