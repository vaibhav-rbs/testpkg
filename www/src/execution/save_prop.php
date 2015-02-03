<?php

$device = $_REQUEST['device'];
$curr_rows = $_REQUEST['curr_rows'];

//$device = "0A3BC2B50B020014";
$file = "/datafiles/propertyfiles/" . $device . ".json";
$file2 = "../../tempdata/property_data/" . $device . ".json";

if ($device != null){
	$fp = fopen($file, 'w');
	fwrite($fp, json_encode($curr_rows));
	fclose($fp);
	$fp = fopen($file2, 'w');
	fwrite($fp, json_encode($curr_rows));
	fclose($fp);

	echo json_encode(array('success'=>true));
} else {
	echo json_encode(array('msg'=>'Missing device info'));
}
?>
