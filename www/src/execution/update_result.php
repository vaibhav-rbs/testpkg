<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/app_log.php');

$user_name = $_REQUEST['user_name'];
$jstring = $_REQUEST['jstring'];
$rowIndex = $_REQUEST['rowIndex'];
//$user_name='fbxj76';
//$rowIndex = 0;
//$jstring = '{"testCaseName":"MDB APENG.ARIA.BAT:007-156","testResult":"P","defectReportId":"","blockedReason":"","executionMethod":"","comments":"dpne","lastUpdUser":"KIM, JUNG SOO (FBXJ76)","lastUpdDate":"2013-10-08T18:30:43.012Z"}';
//$jstring = '[{"testCaseName":"MDB APENG.ARIA.BAT:009-001","testResult":"F","defectReportId":"","blockedReason":"","executionMethod":"Manual","comments":"U123","lastUpdUser":"KIM, JUNG SOO (FBXJ76)","lastUpdDate":"2013-10-07T21:29:08.735Z"},{"testCaseName":"MDB APENG.ARIA.BAT:009-003","testResult":"F","defectReportId":"","blockedReason":"","executionMethod":"Manual","comments":"Ha Ha","lastUpdUser":"KIM, JUNG SOO (FBXJ76)","lastUpdDate":"2013-10-07T21:29:08.735Z"}]';

// update temporary json file
update_temp_json('../../tempdata/testexec/' . $user_name . '_testexeclist.json', $rowIndex, $jstring);

/*
 * update_temp_json
 */
function update_temp_json($file, $index, $jstring) {
	// read from temp json
        $json = file_get_contents($file);
	$fp = fopen($file, 'w');
	if(flock($fp, LOCK_EX)){
		//$json = fread($fp, filesize($file));
	
	$update = json_decode($jstring);
	
	$array_json = json_decode($json, true);
	$testplan = $array_json["testplan"];
	$source = $array_json["rows"];
	
	$source[$index] = $update;
	
	/*
	foreach ($update as $upd) {
		foreach ($source as $key => $value) {
			foreach ($value as $row) {
				if ($row == $upd->testCaseName) {
					$source[$key] = $upd;
					break;
				}
			}
		}*/
	
		// write to temp json
		$result['testplan'] = $testplan;
		$result['rows'] = $source;
		
		fwrite($fp, json_encode($result));
		flock($fp, LOCK_UN);
	}
	fclose($fp);
}
?>