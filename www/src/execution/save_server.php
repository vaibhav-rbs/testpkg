<?php

$name = $_REQUEST['name'];
$status = $_REQUEST['status'];
$ip = $_REQUEST['ip'];
$home = $_REQUEST['home'];

$string = file_get_contents("../../tempdata/testservers.json");
$json_a = json_decode($string, true);
$row_array = $json_a["rows"];
$num = count($row_array);
//var_dump($row_array);
$new_row = array("name"=>"$name", "status"=>"$status", "ip"=>"$ip", "home"=>"$home");
//var_dump($new_row);
if ($name != null){
	$json_a["rows"][$num] = $new_row;
        $json_a["total"] = $num+1;
	$fp = fopen('../../tempdata/testservers.json', 'w');
	fwrite($fp, json_encode($json_a));
	fclose($fp);
	echo json_encode(array('success'=>true));
} else {
	echo json_encode(array('msg'=>'Some errors occured.'));
}


?>
