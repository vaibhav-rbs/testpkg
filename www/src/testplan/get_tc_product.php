<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP


$group=$_REQUEST['group'];
//$group="MD Advance Platforms";
//$group="Emerging Communications Group";


//list($org, $n2, $n3) = split(" ", $group);


// Test central provide the whole list of product, but not base on orgnization. So there are many exist plans has product name which is not in their org, so we return the while list of product//
$org = null;

$xml = simplexml_load_string(GetProductByOrg($org));



if(count($xml->Table) > 0){
	$result = array();
	$result[0]["id"] = 0;
	$result[0]["text"] = "--Select product--";
	$result[0]["selected"] = true;

	$id_num = 1;
	foreach ($xml->Table as $tableList){
		$ele = array();
		$ele['id'] = $id_num;
		$ele['text'] = trim($tableList->Product);

		array_push($result, $ele);
		$id_num++;
	}

	echo json_encode($result);
}else{
	echo json_encode(array('msg'=>'Empty info from Test Central'));
}



function GetProductByOrg($org){

	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	$executionServiceWsdl = new SOAP_WSDL($executionServiceWsdlUrl);
	$executionServiceClient = $executionServiceWsdl -> getProxy();
	$executionServiceClient -> setOpt('timeout', 200);
	$ret = $executionServiceClient -> Interface_GetProducts($org);
	return $ret;
}

?>
