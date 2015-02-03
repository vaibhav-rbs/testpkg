<?php

$package = $_REQUEST['package'];
$method = $_REQUEST['method'];

//$package = "com.moto.android.apython.app.contacts";
//$method = "CreateContact";

$db_server = "localhost";
$db_user = "root";
$db_pass = "root123";
$db_name = "invaderPlusDb";


$dbObject = new mysqli($db_server, $db_user, $db_pass, $db_name);

//select test_driverfile from Testlibrary, Packages where Testlibrary.package_id = Packages.package_id and package_name = 'com.moto.android.apython.app.contacts' and test_methodname='CreateContact';
$my_result = $dbObject->query("select test_driverfile from Testlibrary, Packages where Testlibrary.package_id = Packages.package_id and package_name = '$package' and test_methodname='$method'");

$row = $my_result->fetch_assoc();
$my_value = $row['test_driverfile'];
$my_result->free();
mysqli_close($dbObject);

if($my_value){
	echo $my_value;
}else{
	echo "";
}






?>
