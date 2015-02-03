<?php


$detail=$_REQUEST['detail'];

$dArray = $detail["Table"];
$count = count($dArray);


//Sorting
$sort_key = array();
for( $i = 0; $i < $count; $i++) array_push($sort_key, $dArray[$i]["testCaseName"]);
array_multisort($sort_key, SORT_ASC, $dArray);




//result = N if it is not run

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

$table_string = "<tr><th width=\"15%\">Component</th><th width=\"6%\">Result</th><th width=\"20%\">Test Name</th><th width=\"53%\">Description</th><th width=\"6%\">View Detail</th></tr>";
for($jj = 0; $jj < $pkg_count ; $jj++){


	for($ii = 0 ; $ii < $count ; $ii++){
		if ($dArray[$ii]["groupTypeValue1"] == $package_list[$jj] && $dArray[$ii]["testResult"] == "P"){
					$table_string = $table_string . "<tr><td>" . $dArray[$ii]["groupTypeValue1"] . "</td><td><FONT COLOR=\"Green\">Passed</FONT></td><td>" . $dArray[$ii]["testCaseName"] . "</td><td>" . $dArray[$ii]["caseDescription"] . "</td><td><a href=\"#" . $dArray[$ii]["testCaseName"] . "\">View</a></td></tr>";
		}				
	}


	for($kk = 0 ; $kk < $count ; $kk++){
		if ($dArray[$kk]["groupTypeValue1"] == $package_list[$jj] && $dArray[$kk]["testResult"] == "F"){
					$table_string = $table_string . "<tr><td>" . $dArray[$kk]["groupTypeValue1"] . "</td><td><FONT COLOR=\"Red\">Failed</FONT></td><td>" . $dArray[$kk]["testCaseName"] . "</td><td>" . $dArray[$kk]["caseDescription"] . "</td><td><a href=\"#" . $dArray[$kk]["testCaseName"] . "\">View</a></td></tr>";
		}		
	}


	for($mm = 0 ; $mm < $count ; $mm++){
		if ($dArray[$mm]["groupTypeValue1"] == $package_list[$jj] && $dArray[$mm]["testResult"] == "B"){
					$table_string = $table_string . "<tr><td>" . $dArray[$mm]["groupTypeValue1"] . "</td><td><FONT COLOR=\"Orange\">Blocked</FONT></td><td>" . $dArray[$mm]["testCaseName"] . "</td><td>" . $dArray[$mm]["caseDescription"] . "</td><td><a href=\"#" . $dArray[$mm]["testCaseName"] . "\">View</a></td></tr>";
		}		
	}


	for($nn = 0 ; $nn < $count ; $nn++){
		if ($dArray[$nn]["groupTypeValue1"] == $package_list[$jj] && $dArray[$nn]["testResult"] == "I"){
					$table_string = $table_string . "<tr><td>" . $dArray[$nn]["groupTypeValue1"] . "</td><td><FONT COLOR=\"Blue\">Indeterminated</FONT></td><td>" . $dArray[$nn]["testCaseName"] . "</td><td>" . $dArray[$nn]["caseDescription"] . "</td><td><a href=\"#" . $dArray[$nn]["testCaseName"] . "\">View</a></td></tr>";
		}		
	}
	for($rr = 0 ; $rr < $count ; $rr++){
		if ($dArray[$rr]["groupTypeValue1"] == $package_list[$jj] && $dArray[$rr]["testResult"] == "N"){
					$table_string = $table_string . "<tr><td>" . $dArray[$rr]["groupTypeValue1"] . "</td><td><FONT COLOR=\"Gray\">Not Run</FONT></td><td>" . $dArray[$rr]["testCaseName"] . "</td><td>" . $dArray[$rr]["caseDescription"] . "</td><td><a href=\"#" . $dArray[$rr]["testCaseName"] . "\">View</a></td></tr>";
		}		
	}
}
	
$rArray = array();
$rArray["display"] = $table_string;

echo json_encode($rArray);

?>
