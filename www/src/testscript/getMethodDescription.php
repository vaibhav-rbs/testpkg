<?php
/**
 * getMethodDescription.php
 * It retrieves method description and examples
 * Jung Soo Kim
 */
include 'testlibDB.php';

$classname = $_REQUEST['classname'];
$methodname = $_REQUEST['methodname'];

getMethodDescription($classname, $methodname); // return description of the method
?>