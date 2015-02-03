<?php

//Created by Snigdha Sivadas wvpg48
//Description : To load the testcases from the test suite

require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/tc_functions.php');  

	$suitename=$_GET['test_suite1'];
	
	$results2 = Get_Test_Case_By_Suite($suitename);
	$tmp = "";
	$xml2= simplexml_load_string($results2);
	
		if (count($xml2->Table) > 0) {
			$test_case_list=$test_case_list."[";
			foreach ($xml2->Table as $node2) {
				$data = $node2->CaseDescription;
				//$temp='{name:"'.$node2->TestCaseName.'",desc:"'.filter_data($data).'"}';
				//$test_case_list=$test_case_list.'{id:"2",text:"'.$node2->TestCaseName.'",attributes:'.$temp.'},' ;
				$test_case_list=$test_case_list.'{id:"2",text:"'.$node2->TestCaseName.'"},' ;
				
			}
		
			$test_case_list=substr($test_case_list,0, strlen($test_case_list)-1);
			$test_case_list=$test_case_list."]";
			$test_case_list=$test_case_list.'';
		}
echo $test_case_list;

										

function filter_data($text)
{   $text = $text.trim('');
    $text=preg_replace("/[^a-z \d : . ( ) \/\/ { }  \/n \/t \/s]*/i", "", $text);
    $newchar = $text;
    return $newchar;
	
}

?>
