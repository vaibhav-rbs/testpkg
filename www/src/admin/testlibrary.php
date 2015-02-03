<?php
/*
 * Author: Snigdha Sivadas
 * Description: Controller of Test library
 */

include('admindb.php');
require_once '../common/define_properties.php';


$type = $_REQUEST['type'];
#$coreid = $_REQUEST['coreid'];


switch ($type) {
	case "save":
		$pname = $_REQUEST['package_name'];
		$tmname = $_REQUEST['test_methodname'];
		$tmeth = $_REQUEST['test_method'];
		$tdesc = $_REQUEST['test_description'];
		$texam = $_REQUEST['test_example'];
		$showf = $_REQUEST['showflag'];
	    $testmodule = new DBmodule();
	   # $data =$testmodule->setAddTestLibrary($pname,$tmname,$tmeth,$tdesc,$texam);
	    $data =$testmodule->setUpdateTestLibrary(-1,$pname,$tmname,$tmeth,$showf,$tdesc,$texam);
	     
	    echo $data;
		break;
	case "edit":
		$id = intval($_REQUEST['id']);
		$tid = $_REQUEST['test_id'];
		#$pid = $_REQUEST['package_id'];
		$pname = $_REQUEST['package_name'];
		$tmname = $_REQUEST['test_methodname'];
		$tmeth = $_REQUEST['test_method'];
		$showf = $_REQUEST['showflag'];
		$tdesc = $_REQUEST['test_description'];
		$texam = $_REQUEST['test_example'];
		$testmodule = new DBmodule();
		$data =$testmodule->setUpdateTestLibrary($tid,$pname,$tmname,$tmeth,$showf,$tdesc,$texam);
		echo $data;
		break;
	case "delete":
		$id = intval($_REQUEST['id']);
		$fid = intval($_REQUEST['fid']);
		$testmodule = new DBmodule();
		$data =$testmodule->setDeleteAddFramework($fid);
		echo $data;
		
		break;
	case "load":
		$pname = $_REQUEST['pname'];
		$tname = $_REQUEST['tname'];
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
	    $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
	    $offset = ($page-1)*$rows;
		$testmodule = new DBmodule();
		$data =$testmodule->getMethodsAdmin($offset,$rows,'wvpg48',$pname,$tname);
		echo json_encode($data);
		break;
	default:
		$pname = $_REQUEST['pname'];
		$tname = $_REQUEST['tname'];
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$testmodule = new DBmodule();
	    $data =$testmodule->getMethodsAdmin($offset, $rows,'wvpg48',$pname,$tname);
		echo json_encode($data);
		break;
}


?>
