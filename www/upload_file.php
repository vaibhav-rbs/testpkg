<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
<title>invader+ upload page</title>

<!-- stylesheet -->
<style type="text/css">
body.org {
        font-family:Arial, Helvetica, Sans-Serif;
        font-size:12px;
        margin:0px;
        height:100%;
}
</style>

<link type="text/css" href="themes/menu.css" rel="stylesheet" />

</head>
<body class="org">
	<!-- Start of Menu -->
	<ul id="menu" style="margin-left: 45px;">
		<li class="logo">
			<img style="float:left;" alt="" src="img/menu_left_invader.png"/>
		</li>
		<li>aPython Script Upload</li>
	</ul>
	<img style="float:left;" alt="" src="img/menu_right.png"/>
	<div style="float:none; clear:both;"></div>
    <!-- End of Menu -->

<?php

/*
$package = "com.moto.android.apython.app.test123";
$method = "test1";
$ofile_name = "test1_JB.py";
$device_ver = "JB";
$copied_file = "ReadEmail.py";
$corid = "crmg76";

$_FILES["file"]["name"] = "ReadEmail.py";
$_FILES["file"]["type"] = "text/x-python-script";
$_FILES["file"]["size"] = 100000;

*/



$package = $_REQUEST['pname'];
$method = $_REQUEST['mname'];
$ofile_name = $_REQUEST['ofile'];
$device_ver = $_POST['sversion'];
$copied_file = $_FILES["file"]["name"];

$corid = $username;

/*
$fp = fopen("/tmp/upload2.txt", 'w');
fwrite($fp, $method . "\n");
fwrite($fp, $package . "\n");
fwrite($fp, $ofile_name . "\n");
fwrite($fp, $device_ver . "\n");
fclose($fp);
*/

$db_server = "localhost";
$db_user = "root";
$db_pass = "root123";
$db_name = "invaderPlusDb";



$error ="";
$done ="";

$corid_dir = "/datafiles/upload_tmp/" . $corid;

$allowedExts = array("py");
$extension = end(explode(".", $_FILES["file"]["name"]));
if (($_FILES["file"]["type"] == "text/x-python-script") && ($_FILES["file"]["size"] < 200000) && in_array($extension, $allowedExts)){
  	if ($_FILES["file"]["error"] > 0)
  	{
		$error = "Return Code: " . $_FILES["file"]["error"] . "<br />";
  	}else{


/*    
    echo "Upload: " . $_FILES["file"]["name"] . "<br />";
    echo "Type: " . $_FILES["file"]["type"] . "<br />";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
    echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
 */  

    		if (file_exists($corid_dir)){
			$cmd = "rm -rf " . $corid_dir;
			exec($cmd);
    		}
		$cmd = "mkdir " . $corid_dir;
		exec($cmd);
		$cmd = "chmod 777 " . $corid_dir;
		exec($cmd);

		$save_file = $corid_dir . "/" . $_FILES["file"]["name"];
    		move_uploaded_file($_FILES["file"]["tmp_name"], $save_file);

		//Check saved python script can be compiled

		$cmd = "python -m compileall " . $corid_dir;
		exec($cmd);
		list($name, $junk) = split("\.", $_FILES["file"]["name"]);
		$name2 = $corid_dir . "/" . $name . ".pyc";
		$cmd = "ls $name2 | wc -l";
		$ret = exec($cmd);
		$ret = chop($ret);
		if ($ret == 0 ){

			$error = "Compilation failed for " . $_FILES["file"]["name"] . "<br />";

		}else{

			// Update DB for py file name

			$nfile_name = $method . "_" . $device_ver . ".py";
			$dbObject = new mysqli($db_server, $db_user, $db_pass, $db_name);
			$dbObject->query("update Testlibrary set test_driverfile= '$nfile_name' where test_methodname = '$method' and package_id = (select package_id from Packages where package_name = '$package')");

			mysqli_close($dbObject);



			//Automatically create LogicalMethod.java


        		$data = getMethodDetails($package,$method);
        		$data1 = getMethodDetails_pack($package);
        		$dsize  = sizeof($data);

        		if ($dsize > 0){
                		$LMJava_path = "";
                		$mdata = $data[0];
                		$LMJava_path = getpackagepath($package);
                		$pclass = $mdata['package_driver'].".java";

                		$ctemplate = readFiles('src/common/'.'logicaltemplate.txt');
                		$cf = $ctemplate;

                		$str = preg_replace('/'.'-INVADERINSERTPACKAGE'.'/', 'package '.$package.';',$cf);
                		$str1 = preg_replace('/'.'INVADERINSERTFOLDER'.'/', $mdata['package_driverpath'],$str);
                		$str2 = preg_replace('/'.'INVADERCLASSNAME'.'/',$mdata['package_driver'] ,$str1);

                		$copydir = $corid_dir."/".$pclass;
                		system ('touch   '.$copydir);
                		system ('chmod 777 '.$copydir);

                		$mtemplate = readFiles('src/common/'.'methodtemplate.txt');
                		$methodstr = "";
                		$mtemplate5="";
                		foreach ($data1 as $pt){
                        		$mtemplate1= preg_replace('/METHODNAME/',$pt['test_methodname'],$mtemplate);
                        		$mtemplate2= preg_replace('/FOLDERNAME/',$pt['package_driverpath'],$mtemplate1);
                        		$mtemplate3= preg_replace('/FILENAME/',$pt['test_driverfile'],$mtemplate2);


                        		if (searchstring($pt['test_method'],'information')){
                                		$mtemplate4= preg_replace('/INFOPARAM/','@Param("information") String information,',$mtemplate3);
                                		$mtemplate5= preg_replace('/PARAMSTRING/','information',$mtemplate4);
                        		}else{

                                		$mtemplate4= preg_replace('/INFOPARAM/','',$mtemplate3);
                                		$mtemplate5= preg_replace('/PARAMSTRING/','""',$mtemplate4);
                        		}

                        		$methodstr = $methodstr ."\n". $mtemplate5;
                		}
                		$mtemplate5= preg_replace('/INVADERINSERTMETHODS/',$methodstr,$str2);
                		//print $mtemplate5;
                		writeFile($copydir,$mtemplate5);
                		$LMJave_path = "/home/invader/test/invaderPlus3/framework/UnifiedAutomationFramework/src/" . $LMJava_path;
                		$LMJave_file = $LMJava_path . "/LogicalMethods.java";
        		}else{
                		//do nothing	
        		}







			//Check in python script  and LogicalMethods.java to gerrit
			$p = array();
			$p = split ("\.", $package);
			$num = count($p);
			$package_name = $p[$num - 1];


			// Prepare checkin python script
			$target_dir = "/home/invader/test/invaderPlus3/framework/apython/samples/" . $package_name;
			$target_dir_parent = "/home/invader/test/invaderPlus3/framework/apython/samples";
			$copied_path = "/datafiles/upload_tmp/" . $username . "/" . $copied_file;
			$nfile_path = $target_dir . "/" . $nfile_name;

			if (file_exists($nfile_path)){
				// git rm first
        			$cmd = "cd " . $target_dir . ";sudo -H -u invader git rm " . $nfile_name;
        			$ret = exec($cmd);
			}

			if(!file_exists($target_dir)){
        			$cmd = "sudo -H -u invader mkdir " . $target_dir;
        			$ret = exec($cmd);
			}

			$cmd = "sudo -H -u invader cp " . $copied_path . " " . $nfile_path;
			$ret = exec($cmd);

			$cmd = "cd " . $target_dir . ";sudo -H -u invader git add " . $nfile_name;
			$ret = exec($cmd);


			// Prepare checkin LogicalMethods.java 
			$target_dir2 = $LMJave_path;
			$copied_path2 = "/datafiles/upload_tmp/" . $username . "/LogicalMethods.java";
			$nfile_path2 = $target_dir2 . "/LogicalMethods.java";
			

			if (file_exists($nfile_path2)){
				// git rm first
        			$cmd = "cd " . $target_dir2 . ";sudo -H -u invader git rm LogicalMethods.java";
        			$ret = exec($cmd);
			}

			if(!file_exists($target_dir2)){
        			$cmd = "sudo -H -u invader mkdir " . $target_dir2;
        			$ret = exec($cmd);
			}

			$cmd = "sudo -H -u invader cp " . $copied_path2 . " " . $nfile_path2;
			$ret = exec($cmd);

			$cmd = "cd " . $target_dir2 . ";sudo -H -u invader git add LogicalMethods.java";
			$ret = exec($cmd);

			$cmd = "cd " . $target_dir_parent . ";sudo -H -u invader git commit -a -m 'auto checkin'";
			$ret = exec($cmd);
			$cmd = "cd " . $target_dir_parent . ";sudo -H -u invader git pull";
			$ret = exec($cmd);
			$cmd = "cd " . $target_dir_parent . ";sudo -H -u invader git push";
			$ret = exec($cmd);

                        $pos = strpos($ret, "fast-forwards");

                        if($pos === false) {
                                $done = "New API for " . $method . " under " . $package . " is created <br />";
                        }else{
                                $error = "Automatic git push failed, because new check in from other account is not merged to source tree yet. Please wait for 7 minutes, then re-do again. This is an issue of gerrit performance. " . "<br />";

				// put back  DB for py file name

				$dbObject = new mysqli($db_server, $db_user, $db_pass, $db_name);
				$dbObject->query("update Testlibrary set test_driverfile= '$ofile_name' where test_methodname = '$method' and package_id = (select package_id from Packages where package_name = '$package')");

				mysqli_close($dbObject);



                        }



		}




  	}
}else{
	$error = "Invalid file format!" . "<br />";
}



// Copy this function from src/testscript/testscriptdb.php, because not able to include this for different level of directory

function getMethodDetails($pack,$method) {
       	// Connect to database
	$db_server = "localhost";
	$db_user = "root";
	$db_pass = "root123";
	$db_name = "invaderPlusDb";

	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_name);


        $result = $mysqli->query("CALL sp_getMethodsDetails('".$pack."','".$method."')");
        $data = array();
        $i=-1;
        //$data = $result->fetch_assoc();
        while ($row = $result->fetch_assoc()) {
                //echo $row['test_methodname']." pack= ".$row['package_name']."test method".$row['test_method'];
                ++$i;
                $data[$i]['test_methodname'] = $row['test_methodname'];
                $data[$i]['package_name'] = $row['package_name'];
                $data[$i]['package_driver'] = $row['package_driver'];
                $data[$i]['test_method'] = $row['test_method'];
                $data[$i]['test_description'] = $row['test_description'];
                $data[$i]['test_driverfile'] = $row['test_driverfile'];
                $data[$i]['package_driverpath'] = $row['package_driverpath'];
                $data[$i]['package_description'] = $row['package_description'];


        }

        $result->free();
        mysqli_close($mysqli);
        return $data;
}



// Copy this function from src/testscript/testscriptdb.php, because not able to include this for different level of directory
function getMethodDetails_pack($pack) {
        // Connect to database
	$db_server = "localhost";
	$db_user = "root";
	$db_pass = "root123";
	$db_name = "invaderPlusDb";
	$mysqli = new mysqli($db_server, $db_user, $db_pass, $db_name);


        $result = $mysqli->query("CALL sp_getMethodsDetails_pack('".$pack."')");
        $data = array();
        $i=-1;
        //$data = $result->fetch_assoc();
        while ($row = $result->fetch_assoc()) {
                //echo $row['test_methodname']." pack= ".$row['package_name']."test method".$row['test_method'];
                ++$i;
                $data[$i]['test_methodname'] = $row['test_methodname'];
                $data[$i]['package_name'] = $row['package_name'];
                $data[$i]['package_driver'] = $row['package_driver'];
                $data[$i]['test_method'] = $row['test_method'];
                $data[$i]['test_description'] = $row['test_description'];
                $data[$i]['test_driverfile'] = $row['test_driverfile'];
                $data[$i]['package_driverpath'] = $row['package_driverpath'];
                $data[$i]['package_description'] = $row['package_description'];


        }

        $result->free();
        mysqli_close($mysqli);
        return $data;
}

function getpackagepath($cf){
        //print "\n in getpackagepath ".$cf;
        $str = preg_replace('/\./', '/',$cf);
        //print $str;
        return $str;

}

function searchstring($in,$subin){
         return strpos($in, $subin);
}

function readFiles($path){

        $fh = fopen($path, 'r');
        $theData = fread($fh, filesize($path));
        fclose($fh);
        //echo $theData;
        return $theData;

}

function writeFile($path,$data){

        $fh = fopen($path, 'w') or die("can't open file");
        fwrite($fh, $data);
        fclose($fh);
}



?>
	<div id="uploadPanel" class="easyui-panel" style="margin-left: 45px; margin-right: 45px;">  
		<div id="layoutUpload" class="easyui-layout" style="width: 100%; height: 100%;" fit="true">
			<h2 align="center"><?php echo $error?></h2>
			<h2 align="center"><?php echo $done?></h2>
		</div>
		<br><br>
		<div align="center"><button type="button" onclick="window.close();">Exit</button></div>
	</div>
</body>
</html>
