<?php
include 'testlibDB.php';

$header = array();

$framework = $_REQUEST['framework'];

$packages = getPackages($framework);

echo json_encode($packages);
?>