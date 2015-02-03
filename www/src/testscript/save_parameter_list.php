<?php


$username = $_REQUEST['username'];
$package  = $_REQUEST['pname'];
$method  = $_REQUEST['mname'];
$parameters  = $_REQUEST['parameters'];

//$package  = "com.moto.android.apython.app.accounts";
//$method  = "addaccounts";

$save_dir = "/datafiles/logfiles/parameter_change";
if(!file_exists($save_dir)){
	$cmd = "mkdir " . $save_dir;
	$ret = exec($cmd);
}

$file = $save_dir . "/" . $username . "_" . $package . "_" . $method;

$fp = fopen($file, 'w');
fwrite($fp, json_encode($parameters));
fclose($fp);

echo json_encode(array('success'=>true));


?>
