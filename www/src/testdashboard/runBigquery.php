<?php
include 'authorize.php';

$queryString = $_POST['queryString'];
$projectId = "motorola.com:sandbox";

$query = new Google_Service_Bigquery_QueryRequest();
$query->setQuery($queryString);
$response = $service->jobs->query($projectId, $query);

$result = array();
$fields = array();

foreach ($response->schema->fields as $f) {
	array_push($fields, $f[name]);
}
	
foreach ($response->rows as $row) {
	$item = array();
	$fieldIndex = 0;
	foreach ($row[f] as $value) {
		$item[$fields[$fieldIndex]] = $value[v];
		$fieldIndex++;
	}
	array_push($result, $item);
}

echo json_encode(array('fields' => $fields, 'rows' => $result));
?>