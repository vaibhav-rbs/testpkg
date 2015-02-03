<?php
require_once 'SOAP/Client.php';
include('../common/app_log.php');

$username = $_REQUEST['user_name'];
$config = $_REQUEST['config'];

$arr_config = json_decode($config, true);

// change to ip address
//$arr_config['url'] = str_replace("jenkins-upgrade.am.mot.com", "10.75.66.15", $arr_config['url']);

// read from temp json
$arr_temp = read_content_file('../../tempdata/testexec/' . $username . '_testjob_temp.json');

// save config
$arr_temp["config"] = $arr_config; 

// save temp test job file
write_content_file('../../tempdata/testexec/' . $username . '_testjob_temp.json', json_encode($arr_temp));

// select test cases to run
$arr_testjob = array();
$arr_testcases = array();

foreach($arr_temp["rows"] as $key => $value) {	
	if (preg_match('/src=[\'\"](.*?)[\'\"]/', $value["run"], $matches)) {
		if ($matches[1] == 'themes/icons/checkmark_12.png') {
			unset($value["run"]);
			array_push($arr_testcases, $value);
		}
	}
}

// save config
foreach($arr_config as $key => $value) {
	$arr_testjob[$key] = $value;
}

$arr_testjob["testplan"] = $arr_temp["testplan"];
$arr_testjob["testcases"] = $arr_testcases;	

write_content_file('/datafiles/testjob/testjob_' . $arr_temp["testplan"] . '_' . $arr_config['scope'] . '.json', json_encode($arr_testjob));

// delete temp file
unlink('../../tempdata/testexec/' . $username . '_testjob_temp.json');
//echo "Test job 'testjob_" . $arr_temp["testplan"] . '_' . $arr_config['scope'] . ".json' is saved\n" .
echo 'Saved';

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