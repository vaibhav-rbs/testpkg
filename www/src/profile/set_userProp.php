<?php

$username = $_REQUEST['username'];
//$username = "crmg76";

$dir1 = "/datafiles/userprofiles/"; 
$dir2 = "../../tempdata/profile_data/"; 


$d_file = $dir2 . "default_profile.json";
$file = $dir1 . $username . "_prop.json";
$file2 = $dir2 . $username . "_prop.json";


if(!file_exists($dir2) || !file_exists($d_file)){
	echo json_encode(array('msg'=>'no default json file'));
	exit(0);
}
if(!file_exists($dir1)){
	$cmd = "mkdir " . $dir1;
	exec($cmd);
	$cmd = "chmod 777 " . $dir1;
	exec($cmd);
	$cmd = "cp " . $d_file . " " . $file;
        exec($cmd);
	$cmd = "cp " . $d_file . " " . $file2;
        exec($cmd);
}else{

	if(!file_exists($file)){

		$cmd = "cp " . $d_file . " " . $file;
        	exec($cmd);
	}
	$cmd2 = "cp " . $file . " " . $file2;
       	exec($cmd2);
}

echo json_encode(array('success'=>true));
?>
