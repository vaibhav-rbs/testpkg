<?php
$selected = $_REQUEST['name'];
$string = file_get_contents("../../tempdata/testservers.json");
$json_a = json_decode($string, true);
$row_array = $json_a["rows"];
$num = count($row_array);
$row_array_new = array();


if ($selected != null){
	for ($i=0; $i < $num ; $i++){
		if($row_array[$i]["name"] != $selected){
			array_push($row_array_new, $row_array[$i]);
		}
	}
	$num_new = count($row_array_new);
	$json_a["total"] = $num_new;
	$json_a["rows"] = $row_array_new;
	$fp = fopen('../../tempdata/testservers.json', 'w');
	fwrite($fp, json_encode($json_a));
	fclose($fp);
	echo json_encode(array('success'=>true));
} else {
	echo json_encode(array('msg'=>'Some errors occured.'));
}

?>
