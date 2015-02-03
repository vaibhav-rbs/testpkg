<?php
$result = array();
$testjobs = scandir('/datafiles/testjob');
$masterPlan = $_REQUEST['plan'];

for($i = 2; $i < count($testjobs); $i++) {
	$option = array();
	$option["id"] = $testjobs[$i];
	$option["text"] = $testjobs[$i];
	
	// if testjob filename contains master plan, add to the list
	if (strpos($testjobs[$i], $masterPlan) !== false) {
		array_push($result, $option);
	}
}

sort($result);

// add title option
$option = array('id' => 0, 'text' => '--Select test job--', 'selected' => true);
array_unshift($result, $option);

echo json_encode($result);
?>