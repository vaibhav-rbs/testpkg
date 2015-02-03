<?php
/**
 * getMethodParameters.php
 * It returns parameters for the requested test id
 * Jung Soo Kim
 * July 29, 2012
 */
include 'testlibDB.php';

$packagename = $_REQUEST['packagename'];
$methodname = $_REQUEST['methodname'];

getMethodParameters($packagename, $methodname);
?>