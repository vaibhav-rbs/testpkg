<?php
include 'testlibDB.php';

$idPackage = $_REQUEST['idPackage'];
$method = $_REQUEST['method'];

echo addMethod($idPackage, $method);
?>