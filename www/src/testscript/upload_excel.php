<?php
require_once 'SOAP/Client.php';
include 'Classes/PHPExcel/IOFactory.php';/** PHPExcel_IOFactory */
include 'update_testcase_functions.php';


$username = $_REQUEST['username'];
$filename = $_FILES["csvfile"]["name"];
$suitename = $_REQUEST['suitename'];


/*
$username = "crmg76";
$filename = "crmg76suite (2).csv";
$suitename = "newsuite.test:072";
*/


if ($_FILES["csvfile"]["size"] < 600001 && $_FILES["csvfile"]["size"] != 0){

	$new_file_size = $_FILES["csvfile"]["size"] . " B";
	$new_file_name = $_FILES["csvfile"]["name"];

	$tmp_file = "/tmp/" . $username . ".csv";

	move_uploaded_file($_FILES["csvfile"]["tmp_name"],$tmp_file);

	$objPHPExcel = PHPExcel_IOFactory::load($tmp_file);
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
	//var_dump($sheetData);
	//print $sheetData[1]["A"] . "\n";


	import_to_TC($sheetData,$suitename);


	$cmd = 'rm $tmp_file';
	exec($cmd);



	echo "Test cases are saved to Test Central<div><a href=\"javascript:void(0)\" class=\"easyui-linkbutton\" onclick=\"self.close()\">OK</a></div>";
}else if ($_FILES["csvfile"]["error"] > 0){
	if ($_FILES["csvfile"]["error"] == 1){
  		echo "File size is too big, should not be bigger than 600KB<br />";

	}else{
  		echo "Failed to upload: Return Code: " . $_FILES["csvfile"]["error"] . "<br />";
	}

}else{
   	echo "File size is too big, should not be bigger than 600KB<br />";
}  



function import_to_TC($Data,$suitename){

	$row_count = count($Data);
	$column_count = count($Data[1]) - 2;

	
	for($jj = 2 ; $jj < $row_count+1 ; $jj++){
		if($Data[$jj]["A"] != null){

			if($jj != 2){
				 save_previous_testcase($content_array,$testname,$testdesc,$suitename);
			}
			$testname = $Data[$jj]["A"];
			$testdesc = $Data[$jj]["B"];
			$content_array = array();
			$col_array = array();
			for($kk = 0; $kk < $column_count ; $kk++){
				if($kk == 0) $mark = "C";
				if($kk == 1) $mark = "D";
				if($kk == 2) $mark = "E";
				if($kk == 3) $mark = "F";
				if($kk == 4) $mark = "G";
				if($kk == 5) $mark = "H";
				if($kk == 6) $mark = "I";
				if($kk == 7) $mark = "J";
				if($kk == 8) $mark = "K";
				if($kk == 9) $mark = "L";
				if($kk == 10) $mark = "M";
				
				$col_array[$Data[1][$mark]] = $Data[$jj][$mark];
			}
			array_push($content_array, $col_array);
		}else{
			$col_array = array();
			for($kk = 0; $kk < $column_count ; $kk++){
				if($kk == 0) $mark = "C";
				if($kk == 1) $mark = "D";
				if($kk == 2) $mark = "E";
				if($kk == 3) $mark = "F";
				if($kk == 4) $mark = "G";
				if($kk == 5) $mark = "H";
				if($kk == 6) $mark = "I";
				if($kk == 7) $mark = "J";
				if($kk == 8) $mark = "K";
				if($kk == 9) $mark = "L";
				if($kk == 10) $mark = "M";
				
				$col_array[$Data[1][$mark]] = $Data[$jj][$mark];
			}
			array_push($content_array, $col_array);
		}
		
	
	}
	save_previous_testcase($content_array,$testname,$testdesc,$suitename);

}



function save_previous_testcase($content_array,$testname,$testdesc,$suitename){

	$content = array();
	$c_count = count($content_array);
	$content["total"] = $c_count;
	$content["rows"] = $content_array;
	$jstring = json_encode($content);

	$execution_method = "Manual";
	$script_path = "";


        //Find out execution method for existing test case
	$execution_info = array();
	$execution_info = get_execution_method_info($suitename);


	$testname_array = array();
	$testname_array = get_list_testname_by_suite($suitename);
	if(in_array($testname,$testname_array)){
		$execution_method = $execution_info[$testname]; //only existing test case keep the original execution method
		save_test_case($testname, $jstring, $testdesc, $execution_method, $script_path,2);
	}else{
		save_test_case($suitename, $jstring, $testdesc, $execution_method, $script_path,2);
	}


}

function get_list_testname_by_suite($suitename){
        $xml = simplexml_load_string(Get_Test_Case_By_Suite($suitename));

	$list_testname = array();
        if(count($xml->Table) > 0){
                foreach ($xml->Table as $tableList){
                        $case_name = trim($tableList->TestCaseName);
			array_push($list_testname,$case_name);
		}
	}
	return $list_testname;
}
function get_execution_method_info($suitename){
        $xml2 = simplexml_load_string(Get_Test_Case_By_Suite($suitename));

	$execution_info = array();
        if(count($xml2->Table) > 0){
                foreach ($xml2->Table as $tableList2){
                        $case_name2 = trim($tableList2->TestCaseName);
                        $execution_method = trim($tableList2->ExecutionMethodName);
			$execution_info[$case_name2] = $execution_method;
		}
	}
	return $execution_info;
}
function Get_Test_Case_By_Suite($suite) {
        //echo $suite;echo 'hardcode';
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
        $executionServiceClient   = $executionServiceWsdl->getProxy();
        $executionServiceClient->setOpt('timeout', 500);
        $executionHistory = $executionServiceClient->Interface_GetCaseGeneralInfo($suite);
        return $executionHistory;
}



?>


