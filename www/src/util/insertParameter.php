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
	/*$sql = "select test_id, test_methodname, test_method from Testlibrary, Packages, Framework where " .
		   "Testlibrary.package_id=Packages.package_id and " .
		   "Packages.framework_id=Framework.framework_id and " .
		   "framework_name='apython' order by test_id asc";*/
	$sql = "SELECT * FROM Testlibrary ORDER BY test_id ASC";
	$rs = $conn->query($sql);
	
	while ($row = $rs->fetch_assoc()) {
		$methodname = $row['test_methodname'];
		$method = $row['test_method'];
		$testid = $row['test_id'];
		
		preg_match_all('/[^\(\)]+/', $method, $matches);  // get parameters
		preg_match_all('/[^\[\]]+/', $matches[0][1], $matches);  // get parameters
		
		if ($matches[0][1] == NULL) {
			$parameters = array();
			$tempArray = preg_split("/,/", $matches[0][0]);
			
			foreach ($tempArray as $item) {
				$item = trim(str_replace("java.lang.String", "", $item));
				$item = trim(str_replace("java.util.List", "", $item));
				$item = trim(str_replace("<>", "", $item));
				array_push($parameters, $item);
			}
			
			$strParameters = join(chr(216), $parameters);
		} else {
			$strParameters = $matches[0][1];
		}
		
		echo "ID=$testid, METHOD=" . $methodname . "<br>PARAMETERS=" . $strParameters ."<br><br>";
		
		$parameters = preg_split("/".chr(216)."/", $strParameters);
		
		foreach ($parameters as $item) {
			if (strlen($item) > 0) {
				preg_match_all('/[^\{\}]+/', $item, $matches);
				
				if (strlen($matches[0][1]) == 0) {
					$options = "";
					$type = "text";
				} else {
					$item = $matches[0][0];
					$options = $matches[0][1];
					$type = "combobox";
				}
				
				$sql = "select * from Parameter where test_id='" . $testid . "' and name='" . $item . "' and type='" . $type . "' and options='" . $options . "'";
				$rs1 = $conn->query($sql);
				$num_rows = $rs1->num_rows;
				
				if ($num_rows == 0) {
					$sql = "INSERT INTO Parameter(test_id, name, type, options) VALUES ('$testid', '$item', '$type', '$options')";
				} else {
					$sql = "UPDATE Parameter SET test_id='$testid', name='$item', type='$type', options='$options' " .
						   "where test_id='$testid' and name='$item' and type='$type' and options='$options'";
				}
				
				$result = $conn->query($sql);
					
				if (!$result) {
					echo "Failed in processing $sql";
					exit;
				} else {
					echo "Processed $sql<br>";
				}
			}
		}
		
		echo "<br><hr/><br>";
		
		//echo "$method<br>$strParameters<br><hr>";
		
		/*
		preg_match_all('/[^\[\]]+/', $method, $matches);
		
		
		
		$delim = "/".chr(216)."/";
		*/
	}
}

mysqli_close($conn);
?>