<?php


$username = $_REQUEST['username'];
$plan = $_REQUEST['plan'];

/*
$username = "crmg76";
//$plan = "Master : Tivo MASTER - Jing May (MD Advance Platforms)";
$plan = "Cycle : demo_product MASTER - MASTER (MD Advance Platforms)";
*/

list($plantype, $planname) = split(" : ", $plan);


$file = "/datafiles/plans_creation_tmp/".$username;
$new_array = array();
if(file_exists($file)){

	$string = file_get_contents($file);
	$json_a = json_decode($string, true);


	$num = count($json_a);
	for ($i = 0 ; $i < $num; $i++){
		if ( ($json_a[$i]["plantype"] == $plantype) && ($json_a[$i]["planname"] == $planname)){
		}else{
			array_push($new_array, $json_a[$i]);
		}
	}
	$fp = fopen($file, 'w');
	fwrite($fp, json_encode($new_array));
	fclose($fp);
	
}

echo json_encode(array('success'=>true));

?>
