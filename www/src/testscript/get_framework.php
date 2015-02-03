<?php

//Created by Snigdha Sivadas wvpg48
//Description : To load the testcases from the test suite
//created Dec 20 2001

require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/tc_functions.php');  
include('testscriptdb.php');

	$testmodule = new Testmodule();
	$data =$testmodule->getFramework();
	
	$strjson="[";
	foreach ($data as $arr) {
		//echo "data= ".$arr;
		$frame_name =trim($arr);
		$strjson = $strjson.'{ id:"0" ,'.'text:"'.$frame_name.'"},';
	}
	
	if(strlen($strjson)>1){
		$strjson = substr($strjson,0,strlen($strjson)-1);
	}
	
	$strjson=$strjson."]";
	echo $strjson;
	
					


?>
