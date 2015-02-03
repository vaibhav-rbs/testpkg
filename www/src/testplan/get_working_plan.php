<?php


$username = $_REQUEST['user_name'];
//$username = "crmg76";
$reArray = array();

$file = "/datafiles/plans_creation_tmp/".$username;

$string = file_get_contents($file);
$json_a = json_decode($string, true);

$result[0]["id"] = 0;
$result[0]["text"] = "--Select a working plan--";
$result[0]["selected"] = true;


$num = count($json_a);
for ($i = 0 ; $i < $num; $i++){
	$result[$i+1]["id"] = $i+1;
	$result[$i+1]["text"] = $json_a[$i]["plantype"] . " : " . $json_a[$i]["planname"];
}

echo json_encode($result);


?>
