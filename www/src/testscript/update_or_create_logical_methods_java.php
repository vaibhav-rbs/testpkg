<?
//September 26st 2012
//update_or_create_logical_methods_java
//Author : Snigdha Sivadas (wvpg48)
//Mysql 



require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/tc_functions.php');  
include('testscriptdb.php');
include('testlibDB.php');
 
$framework = $_REQUEST['fname'];
$package  = $_REQUEST['pname'];
$methodname  = $_REQUEST['mname'];
$pyfile = $_REQUEST['pyname'];
$user_name = $_REQUEST['user_name'];


	/*$package  = 'com.moto.android.apython.app.contacts';
	$method  = 'addContact';
	$pyfile = 'tst.py';*/

	/*$method  = 'editContactName';
	$pyfile = 'TestEditContactName.py';
	$user_name = 'wvpg48';*/

	$testmodule = new Testmodule();
	#$data =$testmodule->updateMethodFilename($package ,$method,$pyfile);
	$data = $testmodule->getMethodDetails($package,$method);
	$data1 = $testmodule->getMethodDetails_pack($package);
	$dsize  = sizeof($data); print $dsize;

	if ($dsize > 0){

     
		$path = "";
	       
		$mdata = $data[0];	 
		
		$path = getpackagepath($package);
		system ('mkdir '.UPLOADTMP);
		system ('mkdir '.UPLOADTMP.$user_name);
		$pclass = $mdata['package_driver'].".java";
		
		$ctemplate = readFiles('../common/'.TEMPLATE);
		$cf = $ctemplate;

		$str = preg_replace('/'.CPACKAGE.'/', 'package '.$package.';',$cf);
	        $str1 = preg_replace('/'.CCLASS.'/', $mdata['package_driverpath'],$str);
		$str2 = preg_replace('/'.CCLASSNAME.'/',$mdata['package_driver'] ,$str1);

		$copydir = UPLOADTMP.$user_name."/".$pclass;
		print 'cp '.'../common/'.TEMPLATE.'  '.$copydir;
		system ('touch   '.$copydir);
	    	system ('chmod 777 '.$copydir);
		#$cf = readFiles($copydir);
		
		#print $str2;
		
		$mtemplate = readFiles('../common/'.TEMPLATE2);
		$methodstr = "";
              	$mtemplate5="";
		foreach ($data1 as $pt){
			$mtemplate1= preg_replace('/METHODNAME/',$pt['test_methodname'],$mtemplate);
		        $mtemplate2= preg_replace('/FOLDERNAME/',$pt['package_driverpath'],$mtemplate1);
		        $mtemplate3= preg_replace('/FILENAME/',$pt['test_driverfile'],$mtemplate2);
			

			if (searchstring($pt['test_method'],'information')){
				$mtemplate4= preg_replace('/INFOPARAM/',INFOPARAM1,$mtemplate3);
				$mtemplate5= preg_replace('/PARAMSTRING/','information',$mtemplate4);
			}
				
			else{
				$mtemplate4= preg_replace('/INFOPARAM/','',$mtemplate3);
				$mtemplate5= preg_replace('/PARAMSTRING/','""',$mtemplate4);	
			}	

			#print $mtemplate5;

			#print_r($pt);


			$methodstr = $methodstr ."\n". $mtemplate5;
		}
		$mtemplate5= preg_replace('/INVADERINSERTMETHODS/',$methodstr,$str2);
		#print $mtemplate5;
		writeFile($copydir,$mtemplate5);
		return $path;
	}       
	else { 
		 print 'ERROR';
		return 'ERROR';
	}



	function getpackagepath($cf){
	         print "\n".$cf;
                $str = preg_replace('/\./', '/',$cf);
                print $str;
                return $str;

        }

        function searchstring($in,$subin){
	 return strpos($in, $subin);
	}
	
	function readFiles($path){
	
	$fh = fopen($path, 'r');
	$theData = fread($fh, filesize($path));
	fclose($fh);
	echo $theData;
	return $theData;
	
	}

	function writeFile($path,$data){
	
	$fh = fopen($path, 'w') or die("can't open file");
	fwrite($fh, $data);
	fclose($fh);
	}


?>
