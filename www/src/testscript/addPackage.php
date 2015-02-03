<?php
include 'testlibDB.php';

$framework = $_REQUEST['framework'];
$package = $_REQUEST['package'];

echo addPackage($framework, $package);
?>