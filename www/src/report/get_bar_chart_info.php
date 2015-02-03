<?php


$detail=$_REQUEST['detail'];

$dArray = $detail["Table"];
$count = count($dArray);

for ($i = 0 ; $i < $count ; $i++){
	if (($dArray[$i]["testResult"] != "P") && ($dArray[$i]["testResult"] != "F") && ($dArray[$i]["testResult"] != "B") && ($dArray[$i]["testResult"] != "I"))
		$dArray[$i]["testResult"] = "N";

}
$package_list = array();

array_push($package_list, $dArray[0]["groupTypeValue1"]);

for ($i =1 ; $i < $count ; $i++) {

	$g_count = count($package_list);
	$flag = 1;
	for ($j = 0; $j < $g_count ; $j++){
		if ($package_list[$j] == $dArray[$i]["groupTypeValue1"]) {
			$flag = 0;
			break;
		}
	}
	if ($flag == 1){
		array_push($package_list, $dArray[$i]["groupTypeValue1"]);
	}		
}

$pkg_count = count($package_list);
$rArray = array();
// Initiate count 
for($k = 0; $k < $pkg_count ; $k++){

	$pkg = $package_list[$k];
	$rArray[$pkg]["P"] = 0;
	$rArray[$pkg]["F"] = 0;
	$rArray[$pkg]["B"] = 0;
	$rArray[$pkg]["I"] = 0;
	$rArray[$pkg]["N"] = 0;
}

// Take each element/testcase in origial input array to count the number of pass, fail, blocked, or indeterminate for diff packages
for($n = 0; $n < $count ; $n++){
	$pkg_name = $dArray[$n]["groupTypeValue1"];
	$result = $dArray[$n]["testResult"];
	$rArray[$pkg_name][$result]++;

}

$rArray2 = array();


// prepare the array to be returned
for ($m = 0; $m < $g_count; $m++){
	$pkg2 = $package_list[$m];
	$rArray2[$m]["name"] = $pkg2;
	$rArray2[$m]["P"] = $rArray[$pkg2]["P"];
	$rArray2[$m]["F"] = $rArray[$pkg2]["F"];
	$rArray2[$m]["B"] = $rArray[$pkg2]["B"];
	$rArray2[$m]["I"] = $rArray[$pkg2]["I"];
	$rArray2[$m]["N"] = $rArray[$pkg2]["N"];

}


echo json_encode($rArray2);

?>
