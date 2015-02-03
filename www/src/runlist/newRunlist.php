<?php
// open temporary json file
if($handler = fopen("../../tempdata/datagrid_data.json", 'w')){
	if(fwrite($handler, '[]') === FALSE){
		echo "Opened new JSON file";
	} else {
		//echo "Cannot write JSON temp file";
	}
} else {
	echo "Cannot open JSON temp file";
}

fclose($handler);
?>