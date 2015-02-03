<?php
/**
 * add the selection to temporary place
 * Jung Soo Kim
 * May 1, 12
 */
$rows = $_POST['rows']; // get the array of added test cases
$temp = array();
$tempfile = "../../tempdata/datagrid_data.json";

// write to temp JSON file
if ($fhandler = fopen($tempfile, "w+")) {
	// read the content of temp JSON file
	$content = fread($fhandler, filesize($tempfile));
	$temp = json_decode($content);
	
	if (fwrite($fhandler, json_encode($rows)) === FALSE) {
		echo "FILE WRITE ERROR: Cannot write to TEMP JSON file (tempdata/datagrid_data.json)";
	} else {
		echo "SUCCESS";
		fclose($fhandler);
	}
} else {
	echo "FILE OPEN ERROR: Cannot open TEMP JSON file (tempdata/datagrid_data.json)";
}
?>