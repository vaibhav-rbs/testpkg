<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/app_log.php');

//$suite_name = "Android:081";
//$jsonString = file_get_contents("../../tempdata/suite.json");

$suite_name = $_POST['suite_name'];
$jsonString = $_POST['suite_info'];

app_log_line("Suite_Name = " . $suite_name);
app_log_line("Suite_Info = " . $jsonString);
// Function name : save_test_suite($suite_name, $jsonString)
// Example:
// $jsonString = file_get_contents("/var/www/TC/jung_soo.json");
// save_test_suite($suite_name, $jsonString);
// $suite_name could be a string of suite name which contain functional area when you want to update existing test suite.
// $suite name could be "" if you want to create a new test suite.
//
// Author: Jing-May

preg_match('/:/', $suite_name) ? save($suite_name, $jsonString) : save('', $jsonString);
//save_test_suite($suite_name, $jsonString);

function save_test_suite($suite_name, $jsonString){

        // parse input json string
	$input_array = json_decode($jsonString, true);
	$t_array = array();
	$t1_array = array();
	$t_array = $input_array["Table"];
	$t1_array = $input_array["Table1"];

	// find out if want to modify suite or create new suite
	if (strpos($suite_name, ':') !== FALSE){
		modify_or_create_suite($suite_name,$t_array,$t1_array);
	} else {
		modify_or_create_suite("",$t_array,$t1_array);
	}
}

/**
 * save
 * @param string $suite_name
 * @param json file $json
 * Jung Soo Kim
 */
function save($suitename, $json) {
	$arr = json_decode($json, true);
	$root = new SimpleXMLElement("<INSERTSUITE></INSERTSUITE>");
	$dataset = $root->addChild('NEW_DATASET');
	$testsuite = $dataset->addChild('TEST_SUITE');
	$testsuite->addChild('TestSuiteName', $suitename);
	
	foreach($arr['Table'] as $key => $value) {
		switch ($key) {
			case 'TestSuiteId':
				break;
			case 'TestSuiteOrderId':
				break;
			case 'GroupId':
				break;
			case 'ReadPermission':
				break;
			case 'WritePermission':
				break;
			case 'OrgId':
				break;
			case 'PhaseId':
				break;
			case 'LastUpdUser':
				break;
			case 'LastUpdUserId':
				break;
			case 'LastUpdDate':
				break;
			case 'StatusId':
				break;
			case 'StatusDescription':
				$testsuite->addChild('SuiteStatusDescription', $value);
				break;
			case 'FunctionalAreaId':
				break;
			case 'UserId':
				break;
			case 'login':
				break;
			case 'ReadPermissionDesc':
				$testsuite->addChild('ReadPermission', $value);
				break;
			case 'WritePermissionDesc':
				$testsuite->addChild('WritePermission', $value);
				break;
			case 'TPSHeaderValue':
				$headers = preg_split('/,/', $value);
				$tpsheader = $testsuite->addChild($key);
				$xmldata = $tpsheader->addChild('XMLDATA');
				$columns = $xmldata->addChild('COLUMNS');
				foreach ($headers as $header) {
					$column = $columns->addChild('Column');
					$column->addAttribute('name', trim($header));	
				}
				break;
			default:
				$testsuite->addChild($key, $value);
				break;
		}	
	}
	
	$dataset = $root->addChild('NEW_DATASET');
	$history = $dataset->addChild('TEST_SUITE_HISTORY');
	$history->addChild('Comments', 'Changes from script');
	$history->addChild('HistoryType', 'Revision History');
	
	$dataset = $root->addChild('NEW_DATASET');
	$testsuitedetail = $dataset->addChild('TEST_SUITE_DETAIL');
	
	foreach($arr['Table1'] as $key => $value) {
		$testsuitedetail->addChild($key, $value);
	}
	
	$xml = ($root->asXML());
	$xml = str_replace('<?xml version="1.0"?>', '', $xml);
	$ret = update_one_xml($xml);
	echo $ret;
}


function modify_or_create_suite($suite_name,$t_array, $t1_array){

	$data_arr = array();
	$data_arr["TestSuiteName"] = $suite_name;
	$data_arr["FunctionalAreaName"] = $t_array["FunctionalAreaName"];
	$data_arr["GroupName"] = $t_array["GroupName"];
	$data_arr["OrgName"] = $t_array["OrgName"];
	$data_arr["PhaseName"] = $t_array["PhaseName"];
	$data_arr["LoginDetail"] = $t_array["LoginDetail"];
	//$data_arr["TPSHeaderValue"] = $t_array["TPSHeaderValue"]; 
	$data_arr["TPSHeaderValue"] = ""; 

	$data_arr["SuiteUserGivenName"] = $t_array["SuiteUserGivenName"];
	$data_arr["ReadPermission"] = $t_array["ReadPermissionDesc"];
	$data_arr["WritePermission"] = $t_array["WritePermissionDesc"];
	$data_arr["SuiteStatusDescription"] = $t_array["StatusDescription"];
	$data_arr["Comments"] = "Changes from script";
	$data_arr["HistoryType"] = "Revision History";
	$data_arr["Abstract"] = $t1_array["Abstract"];
	$data_arr["Procedures"] = $t1_array["Procedures"];
	$data_arr["Notes"] = $t1_array["Notes"];
	$data_arr["DocumentPath"] = "tc.mot.com";


	if ($suite_name){
		update_to_TPS_file($t_array["TPSHeaderValue"], $suite_name, $t_array["GroupName"]);
	}
	update_to_TC($data_arr);

}

/*
 * Let's not use this function because this function is called within testscripttreeBySuite.php (JungSOO)

??*/

function update_to_TPS_file($columns, $suite_name, $group_name){

	app_log_line("update_to_TPS_file() is called");
	$key = $group_name . "^" . $suite_name;
	$value = $columns;

	$file_saved = "/datafiles/logfiles/columns_by_suite.json";
	if(file_exists($file_saved)){
		$fp = fopen($file_saved, "r+");
                while (1) {
			if(flock($fp, LOCK_EX)) {  // acquire an exclusive lock
                        	$column_string = fread($fp, filesize($file_saved));
				$column_arr = json_decode($column_string, true);
				$column_arr[$key] = $value;
                        	ftruncate($fp, 0);      // truncate file
				rewind($fp);
				fwrite($fp, json_encode($column_arr));
                        	flock($fp, LOCK_UN);    // release the lock
				fclose($fp);
				break;
			}
			sleep(1);
                }

	}else{
		$column_arr = array();
		$column_arr[$key] = $value;
		$fp = fopen($file_saved, 'w');
		fwrite($fp, json_encode($column_arr));
		fclose($fp);
	}
}

function update_to_TC($data_arr){

	app_log_line("update_to_TC is called() is called");
	app_log_line("LoginDetail=" . $data_arr["LoginDetail"]);
	app_log_line("TPSHeaderValue=" . $data_arr["TPSHeaderValue"]);

	$data1 = array(
		"TEST_SUITE"=>array(
		"TestSuiteName"=>$data_arr["TestSuiteName"],
		"FunctionalAreaName"=>$data_arr["FunctionalAreaName"],
		"GroupName"=>$data_arr["GroupName"],
		"OrgName"=>$data_arr["OrgName"],
		"PhaseName"=>$data_arr["PhaseName"],
		"LoginDetail"=>$data_arr["LoginDetail"],
		"TPSHeaderValue"=>$data_arr["TPSHeaderValue"],
		"SuiteUserGivenName"=>$data_arr["SuiteUserGivenName"],
		"ReadPermission"=>$data_arr["ReadPermission"],
		"WritePermission"=>$data_arr["WritePermission"],
		"SuiteStatusDescription"=>$data_arr["SuiteStatusDescription"]
		)
	);
	
	$data2 = array(
		"TEST_SUITE_HISTORY"=>array(
		"Comments"=>$data_arr["Comments"],
		"HistoryType"=>$data_arr["HistoryType"]
		)
	);
	$data3 = array(
		"TEST_SUITE_DETAIL"=>array(
		"Abstract"=>$data_arr["Abstract"],
		"Procedures"=>$data_arr["Procedures"],
		"Notes"=>$data_arr["Notes"],
		"DocumentPath"=>$data_arr["DocumentPath"]
		)
	);

	$xml = new XmlWriter();
	$xml->openMemory();
	$xml->startElement('INSERTSUITE');
	$xml->startElement('NEW_DATASET');
	write($xml, $data1);
	$xml->endElement();
	$xml->startElement('NEW_DATASET');
	write($xml, $data2);
	$xml->endElement();
	$xml->startElement('NEW_DATASET');
	write($xml, $data3);
	$xml->endElement();
	$xml->startElement('NEW_DATASET');
	$xml->endElement();
	$xml->endElement();
	$xml_string = $xml->outputMemory(true);
	app_log_line($xml_string);	
	$ret = update_one_xml($xml_string);
	app_log_line("Return code from TC : " . $ret);	
	echo $ret . "\n";

}


function update_one_xml($xml_in) {
	//$xml_in = '<INSERTSUITE><NEW_DATASET><TEST_SUITE><TestSuiteName>Android:081</TestSuiteName><FunctionalAreaName>Android</FunctionalAreaName><GroupName>MD Advance Platforms</GroupName><OrgName>MD</OrgName><PhaseName>Advance</PhaseName><LoginDetail>Wang, Jing.May (CRMG76)</LoginDetail><TPSHeaderValue></TPSHeaderValue><SuiteUserGivenName>Jungsoo test suite, new (2)</SuiteUserGivenName><ReadPermission>Motorola Only</ReadPermission><WritePermission>Group Only</WritePermission><SuiteStatusDescription>Verified</SuiteStatusDescription></TEST_SUITE></NEW_DATASET><NEW_DATASET><TEST_SUITE_HISTORY><Comments>Changes from script</Comments><HistoryType>Revision History</HistoryType></TEST_SUITE_HISTORY></NEW_DATASET><NEW_DATASET><TEST_SUITE_DETAIL><Abstract>Hi, there</Abstract><Procedures>Eat Sleep</Procedures><Notes>Hello Jing</Notes><DocumentPath>tc.mot.com</DocumentPath></TEST_SUITE_DETAIL></NEW_DATASET><NEW_DATASET/></INSERTSUITE>';
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$return_code = $executionServiceClient->Interface_SaveTestSuite($xml_in);
	//Jing-May add sleep After return of test central API
	sleep(2);
	return $return_code;
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

?>
