<?php


$corid=$_REQUEST['username'];
$plan=$_REQUEST['plan'];
$all_tests=$_REQUEST['all_tests'];


/*
$corid="crmg76";
$plan="Master : Tivo MASTER - Jing May (MD Advance Platforms)";
$all_tests="{\"total\":7,\"rows\":[{\"name\":\"Test:081-001\",\"description\":\"New changed?description?- JK Tested no supress\"},{\"name\":\"Test:081-002\",\"description\":\"new test case 2\"},{\"name\":\"Test:081-003\",\"description\":\"Enter description here\"},{\"name\":\"Test:081-004\",\"description\":\"tc 4\"},{\"name\":\"Test:081-005\",\"description\":\"test case 005\"},{\"name\":\"Test:081-006\",\"description\":\"006\"},{\"name\":\"Test:081-007\",\"description\":\"006\"}]}";
*/

list($plantype, $planname) = split(" : ",$plan);

$json_a = json_decode($all_tests, true);
if ($json_a["total"] == 0){
	echo json_encode(array('msg'=>'No test cases'));
}else{
	$num_tests = count($json_a["rows"]);
	$row_array = $json_a["rows"];
	$testcases = array();
	for($j = 0 ; $j < $num_tests ; $j++){
		$testcases[$j]["name"] = $row_array[$j]["name"];
		$testcases[$j]["description"] = $row_array[$j]["description"];
	}

	$local_plan_file = "/datafiles/plans_creation_tmp/" . $corid;
	if(file_exists($local_plan_file)){
		$localString = file_get_contents($local_plan_file);
		$local_a = json_decode($localString, true);
		$a_count = count($local_a);
		$flag = 0;
		for($i = 0; $i < $a_count ; $i++){
			if ($local_a[$i]["planname"] == $planname && $local_a[$i]["plantype"] == $plantype){
				$flag = 1;
				$target = $i;
				break;
			}
		}
		if($flag == 1){
			$local_a[$target]["testcases"] = $testcases;
			$fp = fopen($local_plan_file, 'w');
			fwrite($fp, json_encode($local_a));
			fclose($fp);
			echo json_encode(array('msg'=>'Test cases are saved locally'));


		}

	}



}

?>
