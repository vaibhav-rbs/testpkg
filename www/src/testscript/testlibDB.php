<?php
/**
 * testlibDB.php
 * This PHP provides functions that access and query data related to test library APIs information
 * Jung Soo Kim
 */
require_once 'SOAP/Client.php';
require_once '../common/define_properties.php';

/**
 * getMethodParameters
 * returns parameters for a given method
 * Jung Soo Kim
 * @param $packagename
 * @param $methodname
 */
function getMethodParameters($packagename, $methodname) {
	$conn = new mysqli(DBHOST, SQLUSER, SQLPASS, DBNAME);
	$result = array();
	
	if (!$conn) {
		die ('Could not connect: ' . mysqli_error());
	} else {
		$sql = "select parameter_id, name, type, options from Packages, Testlibrary, Parameter where " .
			   "Packages.package_id = Testlibrary.package_id and Testlibrary.test_id = Parameter.test_id and " .
			   "package_name='$packagename' and test_methodname='$methodname' order by parameter_id asc";
		
		$rs = $conn->query($sql);
		
		while ($row = $rs->fetch_assoc()) {
			$item = array();
			$item['name'] = $row['name'];
			$item['type'] = $row['type'];
			$item['options'] = $row['options'];
			$item['id'] = $row['parameter_id'];
			
			/*
			if ($item['type'] == "combobox") {
				$options = array();
			
				foreach (preg_split("/\|/", $row['options']) as $value) { // create option array
					$option = array();
					$option['id'] = $value;
					$option['name'] = $value;
					
					array_push($options, $option);
				}
				
				$item['options'] = $options;;
			} else {
				$item['options'] = $row['options'];	
			}*/
			
			array_push($result, $item);
		}
		
		echo json_encode($result);
		
		mysqli_close($conn);
	}
}

/**
 * getMethodDescription($classname, $methodname)
 * Returns description of the method
 * Jung Soo Kim
 * @param unknown_type $classname
 * @param unknown_type $methodname
 */
function getMethodDescription($classname, $methodname) {
	$conn = new mysqli(DBHOST, SQLUSER, SQLPASS, DBNAME);
	$result = array();
	
	if (!$conn) {
		die ('Could not connect: ' . mysqli_error());
	} else {
		$sql = "select test_description, test_example from Packages join Testlibrary on Packages.package_id=Testlibrary.package_id " .
			   "where test_methodname='$methodname' and package_name='$classname'";
		$rs = $conn->query($sql);
		
		while ($row = $rs->fetch_assoc()) {
			$item = array();
			$item['description'] = $row['test_description'];
			$item['example'] = $row['test_example'];
			
			array_push($result, $item);
		}
		
		echo json_encode($result);
		
		mysqli_close($conn);
	}
}

/**
 * getMethods($package)
 * It queries test library methods for a given package.
 * Jung Soo Kim
 * @param unknown_type $package
 */
function getMethods($package){
    $conn = new mysqli(DBHOST, SQLUSER, SQLPASS, DBNAME);
    $result = array();
    
    if (!$conn) {
    	die ('Could not connect: ' . mysqli_error());
    } else {
    	$sql = "SELECT test_id, test_methodname, test_description, test_example FROM Testlibrary WHERE package_id = '$package' and showflag = 'y' order by test_methodname asc";
    	$rs = $conn->query($sql);
    	
    	while ($row = $rs->fetch_assoc()) {
    		$node = array();
    		$node['id'] = $row['test_id'];
    		$node['text'] = $row['test_methodname'];
    		$node['iconCls'] = 'icon-cog';
    		$node['attributes']['description'] = $row['test_description'];
    		$node['attributes']['example'] = $row['test_example'];
    		
    		array_push($result, $node);
    	}
    	
    	return $result;
    	
    	mysqli_close($conn);
    }
}

/**
 * getPackages
 * It queries packages for a given framework
 * Jung Soo Kim
 * @param $framework
 */
function getPackages($framework){
	$conn = new mysqli(DBHOST, SQLUSER, SQLPASS, DBNAME);
	$result = array();

	if (!$conn) {
		die ('Cound not connect: ' . mysqli_error());
	} else {
		$sql = "SELECT * FROM Packages WHERE framework_id = '" . $framework . "' order by package_name asc";
		$rs = $conn->query($sql);
		
		while ($row = $rs->fetch_assoc()) {
			$node = array();
			$node['id'] = $row['package_id'];
			$node['text'] = $row['package_name'];
			$node['state'] = 'closed';
			
			array_push($result, $node);
		}
		
		return $result;
		
		mysqli_close($conn);
	}
}

/**
 * addPackages($framework, $newPackage)
 * add package
 * @param $framework
 * @param $newPackage
 */
function addPackage($framework, $newPackage) {
	$conn = new mysqli(DBHOST, SQLUSER, SQLPASS, DBNAME);  // connect to database
	
	if (!$conn) {
		die ("Could not connect: " . mysqli_error());
	} else {
		$sql = "SELECT count(*) from Packages where framework_id='$framework' and package_name='$newPackage'";
		$rs = $conn->query($sql);
		$row = $rs->fetch_row();
		
		if ($row[0] == 0) {
			#$sql = "INSERT INTO Packages(framework_id, package_name, package_description, package_publish, package_driver) VALUES (" .
			 #  "'" . $framework . "','" . $newPackage . "','','y','LogicalMethods')";

			$att = $newPackage;
			$pie = explode('.', $att);
			$foldername = $pie[sizeof($pie)-1];
			#print "data".$pie[sizeof($pie)-1];

			$sql = "INSERT INTO Packages(framework_id, package_name, package_description, package_publish, package_driver,package_driverpath) VALUES (" .
			   "'" . $framework . "','" . $newPackage . "','','y','LogicalMethods','".$foldername."')";
		
			
		
			echo $conn->query($sql);
		} else {
			echo "'$newPackage' already exists.";
		}
		
		mysqli_close($conn);
	}
}

/**
 * addMethod
 * add method
 * @param $idPackage
 * @param $strMethod
 */
function addMethod($idPackage, $strMethod) {
	$conn = new mysqli(DBHOST, SQLUSER, SQLPASS, DBNAME); // connect to database
	
	if (!$conn) {
		die ("Could not connect: " . mysqli_error());
	} else {
		$sql = "SELECT count(*) from Testlibrary where package_id='$idPackage' and test_methodname='$strMethod'";
		$rs = $conn->query($sql);
		$row = $rs->fetch_row();
		
		if ($row[0] == 0) {
			$sql = "INSERT INTO Testlibrary(package_id, test_methodname, test_method, test_description, test_example, showflag) VALUES (" .
				   "'$idPackage','$strMethod','','','','y')";
			echo $conn->query($sql);
		} else {
			echo "'$strMethod' already exists.";
		}
		
		mysqli_close($conn);
	}
}

/**
 * updateMethod
 * update test API method
 * @param unknown_type $id
 * @param unknown_type $description
 * @param unknown_type $example
 * @param unknown_type $parameters
 */
function updateMethod($id, $description, $example, $parameters) {
	$conn = new mysqli(DBHOST, SQLUSER, SQLPASS, DBNAME);
	
	if (!$conn) {
		die ("Could not connect: " . mysqli_error());
	} else {
		$sql = "UPDATE Testlibrary SET test_description='$description',test_example='$example' WHERE test_id='$id'";
		
		echo $conn->query($sql); // update description & example
		
		$sql = "SELECT parameter_id FROM Parameter WHERE test_id='$id'";
		$rs = $conn->query($sql);  // get parameters
		
		while ($row = $rs->fetch_assoc()) {
			$key = searchKey($row['parameter_id'], $parameters);

			if ($key === -1) {
				$sql = "DELETE FROM `Parameter` WHERE parameter_id='" . $row['parameter_id'] . "'";
				echo $conn->query($sql);
				//echo "$sql\n";
			}
		}
		
		foreach ($parameters as $p) {
			$p[id] ? $sql = "UPDATE Parameter SET name='$p[name]',type='$p[type]',options='$p[options]' WHERE parameter_id='$p[id]'" :
				     $sql = "INSERT INTO Parameter(test_id, name, type, options) VALUES ('$id','$p[name]','$p[type]','$p[options]')";
			echo $conn->query($sql);  // update parameters
			//echo "$sql\n";
		}
		
		mysqli_close($conn);
	}
}

/**
 * searchKey
 * return key if value is found.  otherwise, return null
 * @param $value
 * @param $array
 */
function searchKey($value, $array) {
	foreach ($array as $key => $val) {
		if ($val['id'] === $value) {
			return $key;
		}
	}
	
	return -1;  // return not found
}
?>