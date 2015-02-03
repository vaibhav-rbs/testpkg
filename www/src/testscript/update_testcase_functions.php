<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../db/api.php');

function save_test_case($test_or_suite, $jsonString, $test_description, $execution_method, $script_path,$flag){
    // parse input json string
	$TPS_array = json_decode($jsonString, true);
	$column_list = array();
	$column_list = array_keys($TPS_array["rows"][0]);
	
	// append another column for script location - Jungsoo
	//array_push($column_list, "Script_Path"); // do not save script path in test case in TC database. Jung Soo
	
	$c_count = count($column_list);
	$r_count = count($TPS_array["rows"]);

	$c_string = "<XMLDATA> <COLUMNS> ";
	for ($i = 0 ; $i < $c_count ; $i++){
		$c_string = $c_string . "<Column name=\"" . $column_list[$i] . "\"/>";
	}
	$c_string = $c_string . "</COLUMNS> </XMLDATA>"; //This is TPS header string

	$cd_string = "<XMLDATA> <COLUMNS> ";
	for($n = 0 ; $n < $c_count ; $n++){
		$c_head = $column_list[$n];
		$cd_string = $cd_string . "<Column name=\"" . $column_list[$n] . "\">";
		for($k = 0 ; $k < $r_count ; $k++){
			$data_array = array();
			$data_array = $TPS_array["rows"][$k];
			
			// save script path in the first row ONLY
			// do not save script path in test case TC database
			/*if ($k == 0) {
				$data_array["Script_Path"] = $script_path;
			}*/
			
			if( $k == ($r_count - 1)){
				$cd_string = $cd_string . $data_array[$c_head] . "</Column>";
			}else{
				$cd_string = $cd_string . $data_array[$c_head] . "\\r\\n";
			}

		}
	}
	$cd_string = $cd_string . "</COLUMNS> </XMLDATA>"; //This is data string of TPS
	//End of parsing json

	//set up all fields for construct input xml
	list($suite_name_left, $right) = split(":", $test_or_suite);
	if (strpos($right, '-') !== FALSE){
		list($suite_name_right, $test_num) = split("-", $right);
		$suite_name = $suite_name_left . ":" . $suite_name_right;
		$test_name = $test_or_suite;
		modify_testcase($suite_name,$test_name,$suite_name_left, $c_string, $cd_string, $test_description, $execution_method,$flag);
	}else{
		$test_num = "";
		$suite_name = $test_or_suite;
		create_new_testcase($suite_name, $suite_name_left, $c_string, $cd_string , $test_description, $execution_method,$flag);
	}

}




function modify_testcase($suite_name,$test_name,$suite_name_left, $c_string, $cd_string, $test_description, $execution_method,$flag){
	$org_suite_info = get_test_suite_general_info($suite_name);
	$org_test_info = get_test_case_detail_info($test_name);

	$xml_ret = simplexml_load_string($org_suite_info);
	$xml_ret2 = simplexml_load_string($org_test_info);

	$data_arr = array();
	$data_arr["TestSuiteName"] = $suite_name;
	$data_arr["FunctionalAreaName"] = $suite_name_left;
	$data_arr["GroupName"] = $xml_ret->Table[0]->GroupName;
	$data_arr["OrgName"] = $xml_ret->Table[0]->OrgName;
	$data_arr["PhaseName"] = $xml_ret->Table[0]->PhaseName;
	$data_arr["LoginDetail"] = $xml_ret->Table[0]->LoginDetail;
	$data_arr["TPSHeaderValue"] = $c_string; 

	$data_arr["SuiteUserGivenName"] = $xml_ret->Table[0]->SuiteUserGivenName;
	$data_arr["ReadPermission"] = $xml_ret->Table[0]->ReadPermissionDesc;
	$data_arr["WritePermission"] = $xml_ret->Table[0]->WritePermissionDesc;
	$data_arr["SuiteStatusDescription"] = $xml_ret->Table[0]->StatusDescription;
	$data_arr["Comments"] = "Changes from script";
	$data_arr["HistoryType"] = "Revision History";
	$data_arr["Abstract"] = $xml_ret->Table1[0]->Abstract;
	$data_arr["Procedures"] = $xml_ret->Table1[0]->Procedures;
	$data_arr["Notes"] = $xml_ret->Table1[0]->Notes;
	$data_arr["DocumentPath"] = "tc.mot.com";
	$data_arr["TestCaseName"] = $test_name;
	$data_arr["DisplayOrderId"] = $xml_ret2->Table[0]->DisplayOrderId;
	$data_arr["VersionId"] = $xml_ret2->Table[0]->VersionId;
	$data_arr["ExecutionMethodDescription"] = $execution_method;


        /*
	if($xml_ret2->Table[0]->ExecutionMethodId == 1){
		$data_arr["ExecutionMethodDescription"] = "Manual";
	}else{
		if ($xml_ret2->Table[0]->ExecutionMethodId == 2){
			$data_arr["ExecutionMethodDescription"] = "Automated";
		}else{
			$data_arr["ExecutionMethodDescription"] = "";
		}	
	}
	*/
	$data_arr["status_id"] = $xml_ret2->Table[0]->StatusId;
	if ($data_arr["status_id"] == 1) {
		$data_arr["CaseStatusDescription"] = "Unknown";
	} elseif ($data_arr["status_id"] == 2) {
		$data_arr["CaseStatusDescription"] = "Under Development";
	} elseif ($data_arr["status_id"] == 3) {
		$data_arr["CaseStatusDescription"] = "Ready for Review";
	} elseif ($data_arr["status_id"] == 4) {
		$data_arr["CaseStatusDescription"] = "Under Review";
	} elseif ($data_arr["status_id"] == 5) {
		$data_arr["CaseStatusDescription"] = "Verified";
	} elseif ($data_arr["status_id"] == 6) {
		$data_arr["CaseStatusDescription"] = "Archived";
	} elseif ($data_arr["status_id"] == 7) {
		$data_arr["CaseStatusDescription"] = "Invalid";
	} elseif ($data_arr["status_id"] == 8) {
		$data_arr["CaseStatusDescription"] = "On Hold";
	}else{
		$data_arr["CaseStatusDescription"] = "";
	}
	$data_arr["AssignedReviewer"] = $xml_ret->Table[0]->LoginDetail; // use LoginDetail for now, the right way is transfering from AssignedReviewerId
	$data_arr["RegressionLevel"] = $xml_ret2->Table[0]->RegressionLevel;
	$data_arr["CaseDescription"] = $test_description;
	$data_arr["TestCaseTPSData"] = $cd_string;

	update_to_TC($data_arr,$flag);

}

function create_new_testcase($suite_name, $suite_name_left, $c_string, $cd_string, $test_description, $execution_method,$flag){
	$data_arr = array();
	$org_testcases = get_test_cases_by_suite($suite_name);
	$xml_ret2 = simplexml_load_string($org_testcases);
	$num_test = count($xml_ret2->{"Table"});
	if($num_test == 0 ){
		$data_arr["DisplayOrderId"] = 1;
		$data_arr["VersionId"] = 1;
	}else{
		$data_arr["DisplayOrderId"] = $num_test + 1;
		$data_arr["VersionId"] = $xml_ret2->Table[0]->VersionId;
	}
	

	$org_suite_info = get_test_suite_general_info($suite_name);
	$xml_ret = simplexml_load_string($org_suite_info);

	$data_arr["TestSuiteName"] = $suite_name;
	$data_arr["FunctionalAreaName"] = $suite_name_left;
	$data_arr["GroupName"] = $xml_ret->Table[0]->GroupName;
	$data_arr["OrgName"] = $xml_ret->Table[0]->OrgName;
	$data_arr["PhaseName"] = $xml_ret->Table[0]->PhaseName;
	$data_arr["LoginDetail"] = $xml_ret->Table[0]->LoginDetail;
	$data_arr["TPSHeaderValue"] = $c_string; 

	$data_arr["SuiteUserGivenName"] = $xml_ret->Table[0]->SuiteUserGivenName;
	$data_arr["ReadPermission"] = $xml_ret->Table[0]->ReadPermissionDesc;
	$data_arr["WritePermission"] = $xml_ret->Table[0]->WritePermissionDesc;
	$data_arr["SuiteStatusDescription"] = $xml_ret->Table[0]->StatusDescription;
	$data_arr["Comments"] = "Changes from script";
	$data_arr["HistoryType"] = "Revision History";
	$data_arr["Abstract"] = $xml_ret->Table1[0]->Abstract;
	$data_arr["Procedures"] = $xml_ret->Table1[0]->Procedures;
	$data_arr["Notes"] = $xml_ret->Table1[0]->Notes;
	$data_arr["DocumentPath"] = "tc.mot.com";
	$data_arr["TestCaseName"] = "";
	$data_arr["ExecutionMethodDescription"] = $execution_method;
	$data_arr["status_id"] = 5;
	$data_arr["CaseStatusDescription"] = "Verified";
	
	$data_arr["AssignedReviewer"] = $xml_ret->Table[0]->LoginDetail; // use LoginDetail for now, the right way is transfering from AssignedReviewerId
	$data_arr["RegressionLevel"] = 1;
	$data_arr["CaseDescription"] = $test_description;
	$data_arr["TestCaseTPSData"] = $cd_string;
	update_to_TC($data_arr,$flag);


}

function update_to_TC($data_arr,$flag){




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
	$data4 = array(
		"TEST_CASE"=>array(
		"TestCaseName"=>$data_arr["TestCaseName"],
		"DisplayOrderId"=>$data_arr["DisplayOrderId"],
		"VersionId"=>$data_arr["VersionId"],
		"ExecutionMethodDescription"=>$data_arr["ExecutionMethodDescription"],
		"CaseStatusDescription"=>$data_arr["CaseStatusDescription"],
		"AssignedReviewer"=>$data_arr["AssignedReviewer"],
		"RegressionLevel"=>$data_arr["RegressionLevel"],
		"CaseDescription"=>$data_arr["CaseDescription"],
		"TestCaseTPSData"=>$data_arr["TestCaseTPSData"]
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
	write($xml, $data4);
	$xml->endElement();

	$xml->endElement();
	$xml_string = $xml->outputMemory(true);


	$ret = update_one_xml($xml_string);
	if($flag == 1){
		echo $ret . "\n";
	}
	



}

function get_test_suite_general_info($suiteName){

	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$return_data = $executionServiceClient->Interface_GetSuiteGeneralInfo($suiteName);

	return $return_data;

}


function get_test_case_detail_info($testName){
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 500);
	$return_data = $executionServiceClient->Interface_GetTestCaseDetailsByTestCase($testName);
	return $return_data;

}


function get_test_cases_by_suite($suite){

	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 500);
	$return_data = $executionServiceClient->Interface_GetCaseGeneralInfo($suite);


	return $return_data;
}




function update_one_xml($xml_in) {
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
	$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient   = $executionServiceWsdl->getProxy();
	$executionServiceClient->setOpt('timeout', 200);
	$return_code = $executionServiceClient->Interface_SaveTestSuite($xml_in);

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
