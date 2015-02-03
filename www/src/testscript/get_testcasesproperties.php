<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
//Created by snigdha Sivadas
//Temp file to append data for multiple testcases
require_once '../common/define_properties.php';
include('testscriptdb.php');
include('methodanalysis.php');

	$currentfile = $_GET['jsonfile_name'];
	$currentappend = $_GET['currentappend'];
	$selectedmethods = $_GET['selectedmethods'];
	
	$path = TESTSCRIPT;
	
	
	
	
	$ty= $currentappend;
	$kyit1 = preg_split("/__/", $ty);
	$len = count($kyit1);
		if($len==2){
			$package=getpackage($kyit1[0]);
			$methodname=getmethod($kyit1[1]);
		}	
	
	
	$testmodule = new Testmodule();
	$data = $testmodule->getMethodDetails($package,$methodname);
	$methodanalysis =  new MethodAnalysis($data[0]);
	$methodanalysis->setTestMethodString($selectedmethods);
	//echo $methodanalysis->printData();
	$newJSON= $methodanalysis->constructJson();
	
	
	echo $newJSON;
	
	
	
	
	
	function getpackage($cf)
	{
		$str = preg_replace('/_/', '.',$cf);
		return $str;
	
	}
	
	function getmethod($cf)
	{
		$str1 = preg_split("/\./", $cf);
		return $str1[0];
	}
	
?>
