<?php


$username = $_REQUEST['username'];
$plan = $_REQUEST['plan'];

/*
$username = "crmg76";
//$plan = "Master : Tivo MASTER - Jing May (MD Advance Platforms)";
$plan = "Cycle : demo_product MASTER - MASTER (MD Advance Platforms)";
*/

list($plantype, $planname) = split(" : ", $plan);


$ret_array = array();
$save_file = "/datafiles/plans_creation_tmp/".$username;
$testcases = array();
if(file_exists($save_file)){



	$string = file_get_contents($save_file);
	$json_a = json_decode($string, true);


	$num = count($json_a);
	$flag = 0;
	for ($i = 0 ; $i < $num; $i++){
		if ( ($json_a[$i]["plantype"] == $plantype) && ($json_a[$i]["planname"] == $planname)){
			$flag = 1;
			$target = $i;
			break;
		}
	}
	if ($flag == 1) {
		if($json_a[$target]["testcases"]){
			$testcases = $json_a[$target]["testcases"];
			$total = count($testcases);
		}else{
			$testcases = null;
			$total = 0;

		}
		$ret_array["total"] = $total;
		$ret_array["rows"] = $testcases;
	}
}

echo json_encode($ret_array);

?>
