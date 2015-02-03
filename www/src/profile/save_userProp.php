<?php

$username = $_REQUEST['username'];
$curr_rows = $_REQUEST['curr_rows'];

$dir1 = "/datafiles/userprofiles/"; 
$dir2 = "../../tempdata/profile_data/"; 


$file = $dir1 . $username . "_prop.json";
$file2 = $dir2 . $username . "_prop.json";


$fp = fopen($file, 'w');
fwrite($fp, json_encode($curr_rows));
fclose($fp);
$fp = fopen($file2, 'w');
fwrite($fp, json_encode($curr_rows));
fclose($fp);

echo json_encode(array('success'=>true));
?>
