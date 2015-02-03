<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('convert_date_tc.php');

$db_server = "localhost";
$db_user = "root";
$db_pass = "root123";
$db_name = "invaderPlusDb";




$execlist = $_REQUEST['execlist'];
$execlist = chop($execlist);
//$execlist = "results_0A3BC2B80900900D_0A3BC2B80900900D_crmg76_Kukak - Invader Demo (MD Advance Platforms) Cycle 1.xml_2012_02_02_15_47_25";

$gdata = $_REQUEST['gdata'];
$row_array = $gdata["rows"];
$num_in_ui = count($row_array);



// get info from TC first

$tmp_arr = array();
$tmp_arr = split("_" , $execlist);
$user_name = $tmp_arr[3];
$d_serial_num = $tmp_arr[1];
$sep = "_" . $user_name . "_";
list($first, $second) = split($sep, $execlist);
list($plan, $runtime_str) = split(".xml_", $second);



$arr_tmp2 = array();
$arr_tmp2 = split("_", $runtime_str);
$runtime_org = "$arr_tmp2[0]-$arr_tmp2[1]-$arr_tmp2[2] $arr_tmp2[3]:$arr_tmp2[4]:$arr_tmp2[5]";
$runtime = convert_date_format($runtime_org);


// get group_type_1 and group_type_2 from test central

$return_info = get_testcase_info($plan);

// debug info 

$xml_ret = simplexml_load_string($return_info);
$num_in_tc = count($xml_ret->Table);

if ($num_in_tc < $num_in_ui){
	echo json_encode(array('msg'=>'check number failed'));

}else{

// upload to TC and update CS
   $flag = 1;
   for( $i =0 ; $i < $num_in_ui ; $i++) {
        $this_test_name = $row_array[$i]["test_case_name"];
	$this_result = $row_array[$i]["test_result"];
	$this_exec_time = $row_array[$i]["exec_time"]; 
	$this_block_reason = $row_array[$i]["block_reason"]; 
	foreach ($xml_ret->Table as $node) {
		if($node->testCaseName == $this_test_name){
			$group_type_1 = $node->groupTypeValue1;
			$group_type_2 = $node->groupTypeValue2;
			$tmp_str = $node->lastUpdDate;
			list($front, $middle) = split("T", $tmp_str);
		        $back = substr($middle, 0, 8);	
			$lasttime = $front . " " . $back;
			
			$lasttime = convert_date_format($lasttime);
			// TC upload result

			$data = array(
                		"TESTRESULT" => array(
                        	"TestCaseName" => "$this_test_name",
                        	"TestPlanName" => "$plan",
                        	"GroupTypeValue1" => "$group_type_1",
                        	"GroupTypeValue2" => "$group_type_2",
                        	"RunDate" => "$runtime",
                        	"TestResult" => "$this_result",
                        	"ExecutionMethodDescription" => "Automated",
                        	"DefectReportId" => "",
                        	"BlockedReason" => "$this_block_reason",
                        	"SetupTime" => "0",
                        	"ExecTime" => "$this_exec_time",
                        	"DebugTime" => "0",
                        	"Comments" => "",
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

			$ret = upload_one_test($xml_string);
			if ($ret != 1){
				$flag = 0;

               			$fp = fopen('/tmp/jing1.txt', 'w');
       				fwrite($fp, "\n");
               			fwrite($fp, "return code from Test Central =" . $ret );
               			fclose($fp);
			}else{
				// debug info
               			$fp = fopen('/tmp/jing1.txt', 'w');
       				fwrite($fp, "\n");
               			fwrite($fp, "return code from Test Central =" . $ret );
               			fclose($fp);

				// CS
        			$dbObject = new mysqli($db_server, $db_user, $db_pass, $db_name);
        			if ($dbObject->connect_errno){
                			//echo "Failed to connect to MySQL: (" . $dbObject->connect_errno . ") " . $dbObject->connect_error;
					echo json_encode(array('msg'=>'mysql DB problem'));
					exit(0);
				}
        			if(!$my_result = $dbObject->query("select id from Execution where corid = '$user_name' and device_serial_num = '$d_serial_num' and run_timestamp = '$runtime_org'")){
					//echo "Select failed: (" . $dbObject->errno . ") " . $dbObject->error;
					echo json_encode(array('msg'=>'mysql DB problem'));
					exit(0);
				}

        			$row = $my_result->fetch_assoc();
        			$my_value = $row['id'];
				$my_result->free();



                		if(!$dbObject->query("update Result set group_type_1 = '$group_type_1', group_type_2 = '$group_type_2', test_result = '$this_result' where execution_id = '$my_value' and test_case_name = '$this_test_name'")){
                        		//echo "update Result table failed: (" . $dbObject->errno . ") " . $dbObject->error;
					echo json_encode(array('msg'=>'mysql DB problem'));
					exit(0);

        			}
				$dbObject->close();
			}

		}
	}
   }
   // Remove temp log files for this user from tempdata/log_data/<user_name>

   $cmd_remove_tmp_log = "rm -rf ../../tempdata/log_data/" . $user_name;
   exec($cmd_remove_tmp_log);


   // remove this entry from tc_ready/$user_name file and tc_ready/all file after upload to Test Central

   if ($flag == 1){
        // take care tc_ready/<corid>
   	$tc_txt = "/datafiles/logfiles/tc_ready/" . $user_name;
   	$tmp_txt = "/tmp/tc_ready_" . $user_name;
   	$infile=fopen($tc_txt,"r");
   	$outfile = fopen($tmp_txt, 'w');
   	while($line = fgets($infile)){
		$line = chop($line);
                if($line != $execlist)
			fwrite($outfile, $line . "\n");
   	}
   	fclose($infile);
   	fclose($outfile);

   	$cmd_back_tc_ready = "cp $tmp_txt $tc_txt";
   	exec($cmd_back_tc_ready);
	chmod($tc_txt, 0777);
	$cmd_clean = "rm $tmp_txt";
	exec($cmd_clean);


        // take care tc_ready/all
	// create /tmp/tc_ready_all_lock file to avoid other thread to read tc_ready/all file at the same time
	//while(1){
		//if(!file_exists('/tmp/tc_ready_all_lock')){
		//	touch('/tmp/tc_ready_all_lock');
   		//	$tc_txt2 = "/datafiles/logfiles/tc_ready/all";
   		//	$tmp_txt2 = "/tmp/tc_ready_all";
   		//	$infile2=fopen($tc_txt2,"r");
   		//	$outfile2 = fopen($tmp_txt2, 'w');
   		//	while($line2 = fgets($infile2)){
		//		$line2 = chop($line2);
               	//		if($line2 != $execlist)
		//			fwrite($outfile2, $line2 . "\n");
   		//	}
   		//	fclose($infile2);
   		//	fclose($outfile2);

   		//	$cmd_back_tc_ready2 = "cp $tmp_txt2 $tc_txt2";
   		//	exec($cmd_back_tc_ready2);
		//	$cmd_clean2 = "rm $tmp_txt2";
		//	exec($cmd_clean2);
		//	unlink('/tmp/tc_ready_all_lock');
		//	break;
		//}else{
	        //	sleep(1);
		//}
	//}		

   	echo json_encode(array('msg'=>"success"));
   }else{ 
	echo json_encode(array('msg'=>'not all testcases uploaded to TC'));
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
?>
