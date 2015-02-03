<?php
$testName = $_GET['testname'];
$testDesc = $_GET['testdesc'];
$list = $_POST['list'];

echo writeToFile($testName, $testDesc, $list);

function writeToFile($testname, $testdesc, $array) {
	// open a file for writing to json file, if it does not exist, create one
	if($handler = fopen("/datafiles/testscriptjson/" . $testname . '.json', 'w+')){
		// encode array to JSON format
		$jsonArray = json_encode($array);
		
		// write json string to the file
		if(fwrite($handler, $jsonArray) === FALSE){
			echo "Cannot write to file ($testname.json)";
			exit;
		} else {
			// if successful, close the handler
			fclose($handle);
		}
	} else {
		echo "Cannot open file ($testname.json)";
		exit;
	}
	
	// open a file for writing to xml file, if it does not exist, create one
	if($handler = fopen("/datafiles/testscriptfiles/" . $testname . '.xml', 'w+')){
		// decode JSON to an array
		$arrayJson = json_decode($jsonArray);
		
		// make a string according to invader+ xml format
		$xmlString = "<test id=\"$testname\" des=\"$testdesc\">";
		
		foreach ($arrayJson as $item) {
			// remove return key
			$targetItem = preg_replace("/\n/", "", $item->target);
			$companionItem = preg_replace("/\n/", "", $item->companion);
			
			if (strlen($targetItem) > 0 And strlen($companionItem) > 0) {
				$xmlString = $xmlString . "<block concurrent=\"true\" id=\"$testname\">";
			} else {
				$xmlString = $xmlString . "<block id=\"$testname\">";
			}
			
			// write target field
			if (strlen($targetItem) > 0) {
				$xmlString = $xmlString .
							"\t<module method=\"" . getMethod($targetItem) . "\" class=\"" . getClass($targetItem) . "\" id=\"$testname\"" .
							getConfig($targetItem, "delay=") . ">" .
							"\t\t<param name=\"deviceID\">TARGET_DEV</param>";
				
				$xmlString = $xmlString . parseParameterValues($targetItem) . "</module>";
			}
			
			// write companion field
			if (strlen($companionItem) > 0) {
				$xmlString = $xmlString .
							 "\t<module method=\"" . getMethod($companionItem) . "\" class=\"" . getClass($companionItem) . "\" id=\"$testname\"" .
							 getConfig($companionItem, "delay=") . ">" .
							 "\t\t<param name=\"deviceID\">COMPANION_DEV</param>";
				
				$xmlString = $xmlString . parseParameterValues($companionItem) . "</module>";
			}
			
			$xmlString = $xmlString . "</block>";
		}
		
		$xmlString = $xmlString . "</test>";
		
		// convert & to &amp; to be compatible with html special characters
		$xmlString = str_replace("&", "&amp;", $xmlString);

		// write xml string to the file
		if(fwrite($handler, $xmlString) === FALSE){
			echo "Cannot write to file ($testname.xml)";
			exit;
		} else {
			// if successful, close the handler
			fclose($handle);
			
			// write xml string to temp XML file for XML Viewer: Script Viewer
			if($handler = fopen("../../tempdata/testscript.xml", 'w+')) {
				if(fwrite($handler, $xmlString) === FALSE){
					echo "Cannot write to temp file (testscript.xml)";
					exit;
				} else {
					fclose($handler);
				}
			} else {
				echo "Cannot open temp file (testscript.xml)";
				exit;
			}
			
			echo "Successfully saved ($testname.xml)";
		}
	} else {
		echo "Cannot open file ($testname.xml)";
		exit;
	}
}

function getConfig($source, $configString) {
	preg_match("/($configString)+[^(<br>)]+/", $source, $matches);
	$configValue = str_replace($configString, "", $matches[0]); // grab value
	
	// return in runlist file format
	$digits = preg_split("/:/", $configValue);
	
	for ($i = 0; $i < count($digits); $i++) {
		$digitVal = (int) $digits[$i];
		
		if ($digitVal > 0) {
			switch ($i) {
				case 0:
					$digitStr = $digitStr . $digitVal . "h";
					break;
				case 1:
					$digitStr = $digitStr . $digitVal . "m";
					break;
				case 2:
					$digitStr = $digitStr . $digitVal . "s";
			}
		}
	}
	
	// if returned digitStr exists, encapsulate it with double quotes (")
	if (strlen($digitStr) > 0) {
		$digitStr = " " . $configString . "\"" . $digitStr . "\"";
	}
	
	return $digitStr;
}

function getClass($source) {
	// get class name
	preg_match("/^(class=)+([^(\<)]+)/", $source, $matches);
	$class = str_replace("class=", "", $matches[0]) . ".LogicalMethods";
	
	return $class;
}

function getMethod($source) {
	// get method name
	preg_match("/(method=)+([^(\<)]+)/", $source, $matches);
	$method = str_replace("method=", "", $matches[0]);
	
	return $method;
}

/**
 * parseParameterValues
 * Jung Soo Kim
 * This function is created to retrieve parameter values according to new version of script editor
 */
function parseParameterValues($source) {
	$parameterValues = array();
	$parameter = preg_split("/<br>/", $source);  // get array from the source string
	
	for ($i = 0; $i < count($parameter); $i++) {
		$parameter[$i] = preg_replace("/(<p)+[^<]+/", "", $parameter[$i]);  // rip off <p> tag
		$parameter[$i] = preg_replace("/<\/p>/", "", $parameter[$i]);  // rip of </p> tag
		$list = preg_split("/=/", $parameter[$i]);
		$paramName = $list[0];  // get parameter name
		$paramVal = $list[1];  // get parameter value
		
		if ($paramName != "class" && $paramName != "method" && $paramName != "delay" && $paramName != null) {
			array_push($parameterValues, $paramVal);  // push to the parameter value array;
		}
	}
	
	if (count($parameterValues) > 0) {
		$result = "<param name=\"information\">". join($parameterValues, "^") . "</param>";  // create parameter value string
	}
	
	/*
	foreach ($parameter as $item) {
		echo $item;exit;
		
		
		$list = preg_split("/=/", $item);
		$paramName = $list[0];  // get parameter name
		$paramVal = $list[1];  // get parameter value
		
		if (sizeof($parameter) > 0) {
			$result = $result;
		}
		
		if ($paramName != "class" && $paramName != "method") {  // filter out non-parameter name
			
		}
	}*/
	return $result;
}

function addParameters($source) {
	// get Parameters
	preg_match("/(<p hidden class=\"ParameterGroup\">).*/", $source, $matches);
	
	if (sizeof($matches) > 0) {
		$parameters = preg_split("/<br>/", $matches[0]);
		
		foreach ($parameters as $pItem) {
			// get parameter name
			preg_match("/^(<p hidden class=\"ParameterGroup\">)+[^<]+/", $pItem, $matches);
			$parameterName = str_replace($matches[1], "", $matches[0]);
		
			// get values
			preg_match("/<\/p>+[^<]+/", $pItem, $matches);
			$parameterValue = str_replace("</p>", "", $matches[0]);
			$parameterValue = substr($parameterValue, strpos($parameterValue, "=") + 1);
		
		
			// add values
			preg_match("/^(<param name=\"information\">).*(<\/param>)/", $output, $matches);
		
			if ($matches == null) {
				// new parameter name
				$output = $output . "<param name=\"$parameterName\">$parameterValue</param>";
			} else {
				preg_match("/(<param name=\"information\">).*(<\/param>)/", $output, $matches);
				$output = str_replace($matches[2], "", $matches[0]) . "^" . $parameterValue . "</param>";
			}
		}	
	}
	
	return $output;
}
?>