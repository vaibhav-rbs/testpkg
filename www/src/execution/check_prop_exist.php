<?php

$device = $_REQUEST['device'];

//$device = "0A3BC2B80900900D";

$property_file = "/datafiles/propertyfiles/" . $device . ".json";



if(!file_exists($property_file)){
	echo json_encode(array('msg'=>'Missing property file for this device:' . $device . ', please set up property file by clicking "Edit Properties" button!'));
}else{
	echo json_encode(array('success'=>true));

}

?>

