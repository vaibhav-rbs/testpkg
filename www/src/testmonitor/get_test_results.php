<?php
require_once 'SOAP/Client.php';

$result = array();
$report_file = $_REQUEST['reportfile'];
//$report_file = "LXAB160062_testjob_XFON MASTER - WISL Stability Rack Pilot Test (MD Android WISL)_201402061340_000001_apython-logs.xml";
$xml_file = "/datafiles/testresult/$report_file";
$path = split('_', str_replace(".xml", '', $report_file));

// last two item in the path array represents folder
$dir = array_pop($path) . '/';
$dir = array_pop($path) . '/' . $dir;
$dir = '/' . implode('_', $path) . '/' . $dir;

if (file_exists($xml_file)) {
	$xml = simplexml_load_file($xml_file);
	
	foreach ($xml->testsuite as $testsuite) {
		foreach ($testsuite->testcase as $testcase) {
			$row = array();
			
			# get attributes of test case
			foreach ($testcase->attributes() as $key => $value) {
				$row[$key] = trim($value);
			}
			
			# add child
			foreach ($testcase->children() as $child) {
				switch ($child->getName()) {
					case 'trace_log':
						if ($child['format'] == 'txt') {
							$url = $dir . trim($child["src"]);
							$search = array('/', ' ', '(', ')');
	    					$replace = array('%2F', '%20', '%28', '%29');
	    					$url = "https://storage.cloud.google.com/testdepot2" . str_replace($search, $replace, $url);
							$row['trace_log'] = $url;
						}
						break;
					case 'error_log':
						if ($child['format'] == 'txt') {
							$url = $dir . trim($child["src"]);
							$search = array('/', ' ', '(', ')');
	    					$replace = array('%2F', '%20', '%28', '%29');
	    					$url = "https://storage.cloud.google.com/testdepot2" . str_replace($search, $replace, $url);
							$row['error_log'] = $url;
						}
						break;
					case 'logcat_main':
						$id = trim($child['id']);
						$url = $dir . trim($child["src"]);
						$search = array('/', ' ', '(', ')');
	    				$replace = array('%2F', '%20', '%28', '%29');
	    				$url = "https://storage.cloud.google.com/testdepot2" . str_replace($search, $replace, $url);
						
	    				# add logcat_main path
	    				if (array_key_exists($id, $row)) {
	    					$property = $row[$id];
	    				} else {
	    					$property = array();
	    				}
	    				
	    				array_push($property, array('logcat_main' => $url));
	    				$row[$id] = $property;
						break;
					case 'screenshot':
						$url = $dir . trim($child["src"]);
						$search = array('/', ' ', '(', ')');
	    				$replace = array('%2F', '%20', '%28', '%29');
	    				$url = "https://storage.cloud.google.com/testdepot2" . str_replace($search, $replace, $url);
						$id = trim($child['id']);
						
						# add screen shot
						if (array_key_exists($id, $row)) {
							$property = $row[$id];
						} else {
							$property = array();
						}
						
						array_push($property, array('screenshot' => $url));
						$row[$id] = $property;
						break;
					case 'screenshot-fc':
						$url = $dir . trim($child["src"]);
						$search = array('/', ' ', '(', ')');
	    				$replace = array('%2F', '%20', '%28', '%29');
	    				$url = "https://storage.cloud.google.com/testdepot2" . str_replace($search, $replace, $url);
						$id = trim($child['id']);
						
						# add screen shot force closure
						if (array_key_exists($id, $row)) {
							$property = $row[$id];
						} else {
							$property = array();
						}
						
						array_push($property, array('screenshot-fc' => $url));
						$row[$id] = $property;
						break;
				}
			}
			
			array_push($result, $row);
		}	
	}
	
	echo json_encode($result);
} else {
	exit ("Failed to open $xml_file");
}
?>