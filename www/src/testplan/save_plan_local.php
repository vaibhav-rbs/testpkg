<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP

$username = $_REQUEST['username'];
$plan = $_REQUEST['plan'];
$plantype = $_REQUEST['plantype'];
$start = $_REQUEST['start'];
$end = $_REQUEST['end'];

/*
$username = "crmg76";
$plantype = "Master";
$plan = "2G Attila MASTER - Ming (MD Advance Platforms)";
$start = "10/4/2013";
$end = "11/4/2013";
*/


//Check if exist in local file already
$save_file = "/datafiles/plans_creation_tmp/".$username;

if(file_exists($save_file)){



	$string = file_get_contents($save_file);
	$json_a = json_decode($string, true);

	//Check if plan already exit


	$num = count($json_a);
	$flag = 0;
	for ($i = 0 ; $i < $num; $i++){
		if ( ($json_a[$i]["plantype"] == $plantype) && ($json_a[$i]["planname"] == $plan)){
			$flag = 1;
			break;
		}
	}
	if ($flag == 1) {
		echo json_encode(array('msg'=>$plantype . " plan " . $plan . " already exists locally"));
		exit(0);
	}
}else{
	$json_a = array();
}

//Check if exist in test central already

if ($plantype == "Master"){
	list($junk, $right) = split(" \(",$plan);
	list($group,$junk2) = split("\)", $right);

	$xml = simplexml_load_string(getMasterPlans($group));

	if(count($xml->Table) > 0){
		$flag2 = 0;
		foreach ($xml->Table as $tableList){
			if(trim($tableList->testplanname) == $plan){
				$flag2 = 1;
				break;
			}
		}
		if($flag2 == 1){
			echo json_encode(array('msg'=>$plantype . " plan " . $plan . " already exists in Test Central, no master plan is saved"));
			exit(0);
		}
	}
}



$data_array = array();

$data_array["planname"] = $plan;
$data_array["plantype"] = $plantype;
$data_array["start"] = $start;
$data_array["end"] = $end;

array_push($json_a, $data_array);


$fp = fopen($save_file, 'w');
fwrite($fp, json_encode($json_a));
fclose($fp);

echo json_encode(array('success'=>true));



function getMasterPlans($groupname){
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
        $executionServiceClient   = $executionServiceWsdl->getProxy();
        $executionServiceClient->setOpt('timeout', 200);
        $executionHistory = $executionServiceClient->Interface_GetTestPlans("Master Plan","Testing","and tp.groupId in(select groupId from groups where groupName = '" . $groupname . "') order by TestPlanName asc");
        return $executionHistory;
}

?>
