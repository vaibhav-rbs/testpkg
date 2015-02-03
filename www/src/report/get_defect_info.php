<?php


$detail=$_REQUEST['detail'];
$defect=$_REQUEST['defect'];


//detail table has most current execution info for test cases
//defect table has history of execution info for test cases

$detail_table = $detail["Table"];
$dt_count = count($detail_table);

$defect_table = $defect["Table"];
$df_count = count($defect_table);

$test_index = array();
for ($i = 0; $i < $dt_count ; $i++){
	for ($j = 0; $j < $df_count; $j++) {
		if ($detail_table[$i]["testCaseName"] == $defect_table[$j]["testCaseName"]){
			$test_index[$i]["testCaseName"] = $detail_table[$i]["testCaseName"];
			$test_index[$i]["index"] = $j;
		}		
	}
}

// $ti_count should be equal to $dt_count which is the real count of test cases of this cycle plan
$ti_count = count($test_index);



$dArray = array();
for ($k = 0 ; $k < $ti_count ; $k++){

	if (isset($test_index[$k]["index"])) {
		$ii = $test_index[$k]["index"];
		$dArray[$k]["testCaseName"] = $defect_table[$ii]["testCaseName"];
		$dArray[$k]["runDate"] = $defect_table[$ii]["runDate"];
		$dArray[$k]["testPlanName"] = $defect_table[$ii]["testPlanName"];
		$dArray[$k]["testResult"] = $defect_table[$ii]["testResult"];
		if (isset($defect_table[$ii]["defectReportId"])){
			$dArray[$k]["defectReportId"] = $defect_table[$ii]["defectReportId"];
		}else{
			$dArray[$k]["defectReportId"] = "";
		}
		if (isset($defect_table[$ii]["blockedReason"])){
			$dArray[$k]["blockedReason"] = $defect_table[$ii]["blockedReason"];
		}else{
			$dArray[$k]["blockedReason"] = "";
		}
		if (isset($defect_table[$ii]["comments"])){
			$dArray[$k]["CRDesc"] = $defect_table[$ii]["comments"];
		}else{
			$dArray[$k]["CRDesc"] = "";
		}
		$dArray[$k]["execTime"] = $defect_table[$ii]["execTime"];
		$dArray[$k]["lastUpdDate"] = $defect_table[$ii]["lastUpdDate"];
	}else{
		$dArray[$k]["testCaseName"] = "";
		$dArray[$k]["runDate"] = "";
		$dArray[$k]["testPlanName"] = "";
		$dArray[$k]["testResult"] = "";
		$dArray[$k]["defectReportId"] = "";
		$dArray[$k]["blockedReason"] = "";
		$dArray[$k]["CRDesc"] = "";
		$dArray[$k]["execTime"] = "";
		$dArray[$k]["lastUpdDate"] = "";
	}
}



$count = count($dArray);

$defect_list = array();

array_push($defect_list, $dArray[0]["defectReportId"]);

for ($i =1 ; $i < $count ; $i++) {

	$g_count = count($defect_list);
	$flag = 1;
	for ($j = 0; $j < $g_count ; $j++){
		if ($defect_list[$j] == $dArray[$i]["defectReportId"]) {
			$flag = 0;
			break;
		}
	}
	if ($flag == 1){
		array_push($defect_list, $dArray[$i]["defectReportId"]);
	}		
}

$defect_count = count($defect_list);

$defect_num = array();
// Initiate the number = 0 for defect_num array
for ($m = 0 ; $m < $defect_count ; $m++){
	$df_id = $defect_list[$m];
	$defect_num[$df_id] = 0;
}

for ($k = 0 ; $k < $count ; $k++){
	for ($n = 0 ; $n < $defect_count ; $n++){
		if ( $dArray[$k]["defectReportId"] == $defect_list[$n]){
			$defectId = $defect_list[$n];
			$defect_num[$defectId]++;
			break;
		}
	}	
	
}



$summary_string = "<tr><th width=\"30%\">Defect ID</th><th width=\"30%\">Number of Associated Test Cases</th></tr>";
for ($s = 0; $s < $defect_count ; $s++){
	$id = $defect_list[$s];
	if ( $id != null)
		$summary_string = $summary_string . "<tr><td>" . $id . "</td><td>" . $defect_num[$id] . "</td></tr>";
}

$detail_string = "<tr><th width=\"15%\">Defect ID</th><th width=\"20%\">Test Name</th><th width=\"39%\">Defect Description</th><th width=\"20%\">Blocked Reason</th><th width=\"6%\">Result</th></tr>";
                                                                                                                       
for($jj = 0; $jj < $defect_count ; $jj++){

	if ( $defect_list[$jj] != null)
	   for($ii = 0 ; $ii < $count ; $ii++){
		if ($dArray[$ii]["defectReportId"] == $defect_list[$jj]){
					$detail_string = $detail_string . "<tr><td>" . $dArray[$ii]["defectReportId"] . "</td><td>" . $dArray[$ii]["testCaseName"] . "</td><td>" . $dArray[$ii]["CRDesc"] . "</td><td>" . $dArray[$ii]["blockedReason"] . "</td><td>" . $dArray[$ii]["testResult"] . "</td></tr>";
		}				
	   }


}
	
$rArray = array();
$rArray["d_summary"] = $summary_string;
$rArray["d_detail"] = $detail_string;

echo json_encode($rArray);

?>
