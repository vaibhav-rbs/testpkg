<?php


$detail=$_REQUEST['detail'];

$dArray = $detail["Table"];
$count = count($dArray);

$result_list = array();

for ($i =0 ; $i < $count ; $i++) {


		array_push($result_list, $dArray[$i]["testResult"]);

}
$num_list = get_number_list($result_list);
$perc_list = get_percent_list($num_list);

$num_list["P2"] = $perc_list["P"];
$num_list["F2"] = $perc_list["F"];
$num_list["B2"] = $perc_list["B"];
$num_list["I2"] = $perc_list["I"];
$num_list["N2"] = $perc_list["N"];

echo json_encode($num_list);



function get_number_list($result_list){

	$nlist = array();
	$nlist["P"] = 0;
	$nlist["F"] = 0;
	$nlist["B"] = 0;
	$nlist["I"] = 0;
	$nlist["N"] = 0;
	$c1 = count($result_list);
	for ($i = 0; $i < $c1 ; $i++){
		if ($result_list[$i] == "P") {
			$nlist["P"]++;
		}elseif($result_list[$i] == "F"){
			$nlist["F"]++;
		}elseif($result_list[$i] == "B"){
			$nlist["B"]++;
		}elseif($result_list[$i] == "I"){
			$nlist["I"]++;
		}else{
			$nlist["N"]++;
		}


	}
	return $nlist;
}	
function get_percent_list($result_sum_list){

	$plist = array();
	$total = $result_sum_list["P"] + $result_sum_list["F"] + $result_sum_list["B"] + $result_sum_list["I"] + $result_sum_list["N"];
	$plist["P"] = round ($result_sum_list["P"]*100/$total);
	$plist["F"] = round ($result_sum_list["F"]*100/$total);
	$plist["B"] = round ($result_sum_list["B"]*100/$total);
	$plist["I"] = round ($result_sum_list["I"]*100/$total);
	$plist["N"] = round ($result_sum_list["N"]*100/$total);
	

	return $plist;
}	




?>
