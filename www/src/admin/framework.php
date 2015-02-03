<?php
/*
 * Author: Snigdha Sivadas
* Description: Controller of Framework
*/

include('admindb.php');
require_once '../common/define_properties.php';


$type = $_REQUEST['type'];
#$type="load";


switch ($type) {
	case "save":
		$framename = $_REQUEST['framework_name'];
	    $testmodule = new DBmodule();
	    $data =$testmodule->setAddFramework($framename);
	    echo $data;
		break;
	case "edit":
		$id = intval($_REQUEST['id']);
		$fid = intval($_REQUEST['fid']);
		$framename = $_REQUEST['framework_name'];
		$testmodule = new DBmodule();
		$data =$testmodule->setupdateAddFramework($fid,$framename);
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
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
	    $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
	    $offset = ($page-1)*$rows;
		$testmodule = new DBmodule();
		$data =$testmodule->getFrameworkAdmin();
		echo json_encode($data);
		break;
	default:
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$offset = ($page-1)*$rows;
		$testmodule = new DBmodule();
	    $data =$testmodule->getFrameworkAdmin();
		echo json_encode($data);
		break;
}


?>