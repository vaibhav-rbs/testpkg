<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/app_log.php');


$user_name = $_REQUEST['user_name'];

//$test_plan = "test (MD Android Sys_test) Cycle 1";
//$jstring = file_get_contents("jung.json");

// get test plan name and changes rows from the temp json file
// Jung Soo Kim
// Oct 7, 2013
$file = '../../tempdata/testexec/' . $user_name . '_testexeclist.json';
$fp = fopen($file, 'r');
if (flock($fp, LOCK_EX)) {
	$json = fread($fp, filesize($file));
        flock($fp, LOCK_UN);
}
fclose($fp);

$array_json = json_decode($json, true);

$test_plan = $array_json['testplan'];
$rows = $array_json['rows'];

// get only updated rows
$update = array();
foreach ($rows as $key => $value) {
	if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z/', $value['lastUpdDate']) == 1) {
		$case_name_tmp = trim(preg_replace('/<img.*?>/', '', $value['testCaseName']));
		$value['testCaseName'] = $case_name_tmp;
		array_push($update, $value);
	}
}

$jstring = json_encode($update);

//$test_plan = $_REQUEST['test_plan']; - JKIM 10/7
//$jstring = $_REQUEST['jstring']; - JKIM 10/7

app_log_line("plan name = " . $test_plan);
app_log_line("Json String = " . $jstring);

// upload results to TestCentral
upload_result_to_tc($test_plan, $jstring);

// delete the temp json file
unlink($file);
echo "1";


function upload_result_to_tc($test_plan, $jstring){

	//get info from jstring

	$in_array = json_decode($jstring, true);
	$count = count($in_array); 

	// get group_type_1 and group_type_2 from test central

	$return_info = get_testcase_info($test_plan);
	$xml_ret = simplexml_load_string($return_info);

	// upload to TC
	for( $i = 0 ; $i < $count ; $i++){
       		$this_test_name = $in_array[$i]["testCaseName"];
		$this_result = $in_array[$i]["testResult"];
		$this_group1 = $in_array[$i]["groupTypeValue1"];
		$tmp_time = $in_array[$i]["lastUpdDate"];
		$runtime = convert_date_format($tmp_time, 2);

		$defectID = $in_array[$i]["defectReportId"];
		$bReason = $in_array[$i]["blockedReason"];
		$tmp_user = $in_array[$i]["lastUpdUser"];
		list($junk, $right) = split('\(', $tmp_user);
		list($user_name, $junk) = split('\)', $right);
		$this_comments = $in_array[$i]["comments"];
		if ($in_array[$i]["executionMethod"] == ""){
			$this_exec_type = "Manual";
		}else{
			$this_exec_type = $in_array[$i]["executionMethod"];
		}
		foreach ($xml_ret->Table as $node) {
			if($node->testCaseName == $this_test_name){
				//$group_type_1 = $node->groupTypeValue1;
				$group_type_2 = $node->groupTypeValue2;
				$tmp_str = $node->lastUpdDate;
				$lasttime = convert_date_format($tmp_str, 1);
				// TC upload result

				$data = array(
               				"TESTRESULT" => array(
                       			"TestCaseName" => "$this_test_name",
                       			"TestPlanName" => "$test_plan",
                       			"GroupTypeValue1" => "$this_group1",
                       			"GroupTypeValue2" => "$group_type_2",
                       			"RunDate" => "$runtime",
                       			"TestResult" => "$this_result",
                       			"ExecutionMethodDescription" => "$this_exec_type",
                       			"DefectReportId" => "$defectID",
                       			"BlockedReason" => "$bReason",
                       			"SetupTime" => "0",
                       			"ExecTime" => "0",
                      			"DebugTime" => "0",
                       			"Comments" => "$this_comments",
                       			"LoginDetail" => "$user_name",
                       			"LastUpdDate" => "$lasttime",
                       			"Status" => "1"
               			)
       				);

       				$xml = new XmlWriter();
       				$xml->openMemory();
       				//$xml->startDocument('1.0', 'UTF-8');
       				$xml->startElement('NEW_DATASET');

       				write($xml, $data);

       				$xml->endElement();
       				$xml_string = $xml->outputMemory(true);
				app_log_line("input xml = " . $xml_string);
				//var_dump($xml_string);
				$ret = upload_one_test($xml_string);
				app_log_line("return code from TC = " . $ret);
				//print "\nReturn=" . $ret . "\n";
				break;
			}
		}
	}

}


// functions

function get_testcase_info($plan_name){

	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient = $executionServiceWsdl -> getProxy();
	$executionServiceClient -> setOpt('timeout', 200);
	$result = $executionServiceClient -> Interface_GetTestCaseInfoByPlan($plan_name);
	return $result;
}


function upload_one_test($xml_in) {

	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_executionService.asmx?WSDL';
	$executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient = $executionServiceWsdl -> getProxy();
	$executionServiceClient -> setOpt('timeout', 200);
	$result = $executionServiceClient -> Interface_UpdateTestResults($xml_in);
	return $result;
}



function write(XMLWriter $xml, $data){
    foreach($data as $key => $value){
        if(is_array($value)){
            $xml->startElement($key);
            write($xml, $value);
            $xml->endElement();
            continue;
        }
        $xml->writeElement($key, $value);
    }
}

function convert_date_format($date_string, $flag) {

	if($flag == 1){
        	list($d_string, $junk) = split("\+", $date_string);
	}else{
        	list($d_string, $junk) = split("\.", $date_string);
	}
        list($date, $time) = split("T", $d_string);
        list($year, $month, $day) = split("-", $date);

        if ($month == "01")
                $month = "Jan";
        if ($month == "02")
                $month = "Feb";
        if ($month == "03")
                $month = "Mar";
        if ($month == "04")
                $month = "Apr";
        if ($month == "05")
                $month = "May";
        if ($month == "06")
                $month = "Jun";
        if ($month == "07")
                $month = "Jul";
        if ($month == "08")
                $month = "Aug";
        if ($month == "09")
                $month = "Sep";
        if ($month == "10")
                $month = "Oct";
        if ($month == "11")
                $month = "Nov";
        if ($month == "12")
                $month = "Dec";

        $date_for_tc = $day . "-" . $month . "-" . $year . " " . $time;

        return $date_for_tc;
}
?>
