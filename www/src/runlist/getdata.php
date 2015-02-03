<?php
//$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
//$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
//$sort = isset($_POST['sort']) ? intval($_POST['sort']) : 'testid';
//$order = isset($_POST['order']) ? intval($_POST['order']) : 'asc';
$page = $_POST['page'];
$rows = $_POST['rows'];
$sort = $_POST['sort'];
$order = $_POST['order'];
$remove = $_POST['remove'];
$offset = ($page - 1) * $rows;
$update = $_POST['updateArray'];
$delay = $_POST['delay'];
$iteration = $_POST['iteration'];
$move = $_POST['move'];
$selection = $_POST['selection'];

$jsonfile = "../../tempdata/datagrid_data.json";

$result = array();
$sort_key = array();
$temp = array();
$items = array();

// read the content of the temp JSON file.
if ($handler = fopen($jsonfile, 'r')){
	$content = fread($handler, filesize($jsonfile));
	fclose($handler);

	$temp = json_decode($content, TRUE);
	
	if ($sort != NULL) {
		// sorting the array
		// collect key values to be used for sort
		foreach ($temp as $value) {
			array_push($sort_key, $value[$sort]);
		}
		
		// sort array based on the order and selected key
		if($order == 'asc'){
			array_multisort($sort_key, SORT_ASC, $temp);
		} else {
			array_multisort($sort_key, SORT_DESC, $temp);
		};
	}
	
	// update portion of array
	foreach ($update as $value) {
		$key = array_search($value, $temp);
		$temp[$key]["delay"] = $delay;
		$temp[$key]["count"] = $iteration;
	}
	
	// remove portion of array
	if (sizeof($remove) > 0) {
		foreach ($remove as $value) {
			$key = array_search($value, $temp);
			
			// remove the item from the array
			$temp = array_remove($temp, $key);
		}
	}
	
	// move the row
	if ($move != NULL) {
		// search the key of the moving row
		$key = array_search($selection, $temp);
		
		switch ($move) {
			case "UP":
				if ($key > 0) {
					$temp[$key] = $temp[$key - 1];
					$temp[$key - 1] = $selection;
				}
			break;
			case "DOWN":
				if ($key < count($temp)) {
					$temp[$key] = $temp[$key + 1];
					$temp[$key + 1] = $selection;
				}
			break;
			case "TOP":
				// remove the selection from the array
				$temp = array_remove($temp, $key);
				
				// put the selection to the top of the array
				array_unshift($temp, $selection);
				break;
			case "BOTTOM":
				// remove the selection from the array
				$temp = array_remove($temp, $key);
				
				// push the selection to the bottom of the array
				array_push($temp, $selection);
		}
	}
	
	// get the size of $temp array
	$sizeTemp = sizeof($temp);
	
	// slide the array by the current page and rows
	$items = array_slice($temp, $offset, $rows);
	
	$result["total"] = $sizeTemp;
	$result["rows"] = $items;
	
	echo json_encode($result);
	
	if ($handler = fopen($jsonfile, 'w')) {
		fwrite($handler, json_encode($temp));
	}
	
	/*
	$jsondebug = "../../tempdata/datagrid_debug.json";
	if ($handlerDebug = fopen($jsondebug, 'w')) {
		fwrite($handlerDebug, json_encode($temp));
	}*/
}

function array_remove($input, $index) {
	if ($index == 0) {
		array_shift($input); // if the remove item is the first
	} else if ($index == sizeof($input) - 1) {
		array_pop($input); // if the remove item is the last
	} else {
		$head = array_slice($input, 0, $index);
		$tail = array_slice($input, $index + 1);
		$input = array_merge($head, $tail); // if the remove item is in between
	}
	
	return $input;
}
?>