<?
//September 21st 2012
//Update db
//Author : Snigdha Sivadas (wvpg48)
//Mysql 



require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/tc_functions.php');  
include('testscriptdb.php');
include('testlibDB.php');
 
$framework = $_REQUEST['fname'];
$package  = $_REQUEST['pname'];
$method  = $_REQUEST['mname'];
$pyfile = $_REQUEST['pyname'];


#$package  = 'com.moto.android.apython.app.contacts';
#$method  = 'addContact';
#$pyfile = 'Testfile.py';
#addPackage(2, 'com.moto.android.apython.app.contacts3');


$testmodule = new Testmodule();
$data =$testmodule->updateMethodFilename($package ,$method,$pyfile);
	




?>
