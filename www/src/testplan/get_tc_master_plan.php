<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP


$group=$_REQUEST['group'];
//$group="MD Advance Platforms";
//$group="Emerging Communications Group";

$xml = simplexml_load_string(getMasterPlans($group));

if(count($xml->Table) > 0){
	$result = array();
	$result[0]["id"] = 0;
	$result[0]["text"] = "--Select master plan--";
	$result[0]["selected"] = true;

	$id_num = 1;
	foreach ($xml->Table as $tableList){
		$ele = array();
		$ele['id'] = $id_num;
		$ele['text'] = trim($tableList->testplanname);

		array_push($result, $ele);
		$id_num++;
	}

	echo json_encode($result);
}else{
	echo json_encode(array('msg'=>'Empty info from Test Central'));
}


function getMasterPlans($groupname){
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
        $executionServiceClient   = $executionServiceWsdl->getProxy();
        $executionServiceClient->setOpt('timeout', 200);
        $executionHistory = $executionServiceClient->Interface_GetTestPlans("Master Plan","Testing","and tp.groupId in(select groupId from groups where groupName = '" . $groupname . "') order by TestPlanName asc");
        return $executionHistory;
}


?>
