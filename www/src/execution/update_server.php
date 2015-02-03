<?php

$selected = $_REQUEST['selected'];
$name = $_REQUEST['name'];
$status = $_REQUEST['status'];
$ip = $_REQUEST['ip'];
$home = $_REQUEST['home'];
	$fp = fopen('/tmp/selected.txt', 'w');
	fwrite($fp, $selected);
	fclose($fp);

$string = file_get_contents("../../tempdata/testservers.json");
$json_a = json_decode($string, true);
$row_array = $json_a["rows"];
$num = count($row_array);
//var_dump($row_array);
$update_row = array("name"=>"$name", "status"=>"$status", "ip"=>"$ip", "home"=>"$home");
//var_dump($new_row);
if ($name != null){
	for ($i=0; $i < $num ; $i++){
		if($row_array[$i]["name"] == $selected){
			$json_a["rows"][$i] = $update_row;
			break;
		}
	}
	$fp = fopen('../../tempdata/testservers.json', 'w');
	fwrite($fp, json_encode($json_a));
	fclose($fp);
	echo json_encode(array('success'=>true));
} else {
	echo json_encode(array('msg'=>'Some errors occured.'));
}



?>
