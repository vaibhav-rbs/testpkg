<?php
#require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
//Created by snigdha Sivadas
//Temp file to append data for multiple testcases
require_once '../common/define_properties.php';
require_once 'testscriptcommon.php';

	$currentfile = $_GET['xmlfile_name'];
	//echo "received".$currentfile;
	$currentfile = getCharactersConvert($currentfile);
	 
	$path = OUTSCRIPTS;
	//echo "get xml".$path.$currentfile;
	if (!file_exists ($path.$currentfile)){
		echo  "FALSE";
		exit;
	}
	else{
	$newXML = file_get_contents($path.$currentfile);
	echo $newXML;
	}
?>
