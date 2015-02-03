<?php
require_once 'SOAP/Client.php';
include('../common/app_log.php');

$file = $_REQUEST['file'];
$config = $_REQUEST['config'];
$arr_config = json_decode($config, true);

// read test job json file
$arrFile = read_content_file('/datafiles/testjob/' . $file);

// save schedule
foreach ($arr_config as $key => $value) {
	$arrFile[$key] = $value;
} 

write_content_file('/datafiles/testjob/' . $file, json_encode($arrFile));


/**
 * functions
 * @param $filename
 */

function read_content_file($filename) {
	$fp = fopen($filename, 'r');

	if ($fp) {
		$array = json_decode(fread($fp, filesize($filename)), true);
		fclose($fp);
	}
	
	return $array;
}

function write_content_file($filename, $content) {
	$directory = dirname($filename);
	
	if(!file_exists($directory)) {
		mkdir($directory, 0777, true);
	}
	
	$fp = fopen($filename, 'w');
	fwrite($fp, $content);
	fclose($fp);	
}

?>