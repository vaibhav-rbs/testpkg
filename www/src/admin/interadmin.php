<?php

include('admindb.php');
require_once '../common/define_properties.php';


$type = $_REQUEST['type'];
//$type='lpack';
$core = 'wvpg48';

switch ($type) {
	case "lframe":
		$framename = $_REQUEST['framework_name'];
	    $testmodule = new DBmodule();
	    $data =$testmodule->setAddFramework($framename);
	    echo $data;
		break;
	case "lpack":
		$testmodule = new DBmodule();
		$data = $testmodule->getPackagesBox('apython',$core);
		echo json_encode($data);
		break;
	default:
		
		$testmodule = new DBmodule();
		$data = $testmodule->getPackagesBox('apython',$core);
		echo json_encode($data);
		break;
		break;
}


?>
