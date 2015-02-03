<?php
/**
 * this php program does data migration with inserting parameters per test method to Parameter table
 * Jungsoo Kim
 * July 27, 2012
 */
require_once '../common/define_properties.php';

$conn = new mysqli(DBHOST, SQLUSER, SQLPASS, DBNAME);

if (!$conn) {
	die ('Count not connect: ' . mysqli_error());
} else {
	$sql = "select test_id, test_methodname, test_method from Testlibrary, Packages, Framework where " .
		   "Testlibrary.package_id=Packages.package_id and " .
		   "Packages.framework_id=Framework.framework_id and " .
		   "framework_name='apython' order by test_id asc";
	$rs = $conn->query($sql);
	
	while ($row = $rs->fetch_assoc()) {
		$replacements = array();
		$patterns = array();
		$methodname = $row['test_methodname'];
		$method = $row['test_method'];
		$testid = $row['test_id'];
		
		echo "testID=$testid<br><br>";
		echo $method."<br><br>";
		
		if (preg_match_all("/GMT.[0-9]:[0-9][0-9]/", $method, $matches) > 0) { // get original values for replacement
			foreach ($matches[0] as $item){
				array_push($replacements, $item);
			}
		}
		
		$method = preg_replace('/:/', chr(216), $method);  // change with special characters
		
		echo $method."<br><br>";
		
		if (preg_match_all("/GMT.[0-9]".chr(216)."[0-9][0-9]/", $method, $matches) > 0) {  // revert to original values
			foreach ($matches[0] as $item){
				array_push($patterns, "/".str_replace("+", "\+", $item)."/");
			}
		}
		
		$method = preg_replace($patterns, $replacements, $method);
		
		echo $method."<br><br>";
		
		echo print_r($replacements)."<br>";
		echo print_r($patterns)."<br><br>";
		
		$sql = "UPDATE Testlibrary SET test_method='$method' WHERE test_id='$testid'";
		$result = $conn->query($sql);
		
		if (!$result) {
			echo "Failed in processing $sql<br><hr/>";
			exit;
		} else {
			echo "Processed $sql<br><hr/>";
		}
	}
}

mysqli_close($conn);
?>