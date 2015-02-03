<?
//Date created 12/16/2011
//Author : Snigdha Sivadas (wvpg48)
//Mysql 


require_once '../common/define_properties.php';

class Testmodule {

    var $dbHost,
        $dbUser,
        $dbName,
        $dbPass,
        $dbUserTable;

    // This is the constructor function definition - it's possible to pass
    // it values just like a normal function, but that isn't demonstrated here
    // These variables will be set for each object that is created using this class
    function Testmodule() {
        $this->dbHost = DBHOST;
        $this->dbUser = SQLUSER;
        $this->dbName = DBNAME;
        $this->dbPass = SQLPASS;
        //$this->dbUserTable = 'usersExample';
    }
    
    function getFramework() {
        // Connect to database
        $mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
		$result = $mysqli->query("CALL sp_getFramework()");
		$data = array();
		
		$i=-1;
		//$data = $result->fetch_assoc();
        while ($row = $result->fetch_assoc()) {
        //echo $row['framework_name'];
        $data[++$i]=$row['framework_name'];
        }
		
		$result->free();
		mysqli_close($mysqli);
    	return $data;
        

    } 
    
    
    function getPackage($pack,$core) {
    	// Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
    	//$result = $mysqli->query("CALL sp_getPackage('".$pack."','".$core."')");
    	$result = $mysqli->query("select package_name from Packages join Framework on Packages.framework_id=Framework.framework_id where framework_name='".$pack."' order by package_name asc");
    	$data = array();
    	$i=-1;
    	//$data = $result->fetch_assoc();
    	while ($row = $result->fetch_assoc()) {
    		//echo $row['framework_name'];
    		$data[++$i]=$row['package_name'];
    	}
    
    	$result->free();
    	mysqli_close($mysqli);
    	return $data;
    
    
    }
    

  	function getMethod($pack) {
    	// Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
    	
    	 
    	//$result = $mysqli->query("CALL sp_getMethods('".$pack."')");
    	$result = $mysqli->query("select test_methodname from Testlibrary join Packages on Testlibrary.package_id=Packages.package_id where package_name='".$pack."' order by test_methodname asc");
    	$data = array();
    	$i=-1;
    	//$data = $result->fetch_assoc();
    	while ($row = $result->fetch_assoc()) {
    		//echo $row['test_methodname']." pack= ".$row['package_name'];
    		++$i;
    		$data[$i][0] = $row['test_methodname'];
    		$data[$i][1] = $row['package_name'];
    	}
    
    	$result->free();
    	mysqli_close($mysqli);
    	return $data;
    
    
    }
    
    function getMethodDetails_fromFramework($pack) {
    	// Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
    	 
    
    	$result = $mysqli->query("CALL sp_getMethodsdeatils_fromFramework('".$pack."')");
    	$data = array();
    	$i=-1;
    	//$data = $result->fetch_assoc();
    	while ($row = $result->fetch_assoc()) {
    		//echo $row['test_methodname']." pack= ".$row['package_name'];
    		++$i;
    		$data[$i][0] = $row['test_methodname'];
    		$data[$i][1] = $row['test_method'];
    		$data[$i][2] = $row['test_description'];
    	}
    
    	$result->free();
    	mysqli_close($mysqli);
    	return $data;
    }    

    function getMethodDetails($pack,$method) {
    	// Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
    	 
    
    	$result = $mysqli->query("CALL sp_getMethodsDetails('".$pack."','".$method."')");
    	$data = array();
    	$i=-1;
    	//$data = $result->fetch_assoc();
    	while ($row = $result->fetch_assoc()) {
    		//echo $row['test_methodname']." pack= ".$row['package_name']."test method".$row['test_method'];
    		++$i;
    		$data[$i]['test_methodname'] = $row['test_methodname'];
    		$data[$i]['package_name'] = $row['package_name'];
    		$data[$i]['package_driver'] = $row['package_driver'];
    		$data[$i]['test_method'] = $row['test_method'];
    		$data[$i]['test_description'] = $row['test_description'];
            $data[$i]['test_driverfile'] = $row['test_driverfile'];
			$data[$i]['package_driverpath'] = $row['package_driverpath'];
			$data[$i]['package_description'] = $row['package_description'];
    	}
    
    	$result->free();
    	mysqli_close($mysqli);
    	return $data;     
    }


   function getMethodDetails_pack($pack) {
    	// Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
    	 
    
    	$result = $mysqli->query("CALL sp_getMethodsDetails_pack('".$pack."')");
    	$data = array();
    	$i=-1;
    	//$data = $result->fetch_assoc();
    	while ($row = $result->fetch_assoc()) {
    		//echo $row['test_methodname']." pack= ".$row['package_name']."test method".$row['test_method'];
    		++$i;
    		$data[$i]['test_methodname'] = $row['test_methodname'];
    		$data[$i]['package_name'] = $row['package_name'];
    		$data[$i]['package_driver'] = $row['package_driver'];
    		$data[$i]['test_method'] = $row['test_method'];
    		$data[$i]['test_description'] = $row['test_description'];
                $data[$i]['test_driverfile'] = $row['test_driverfile'];
		$data[$i]['package_driverpath'] = $row['package_driverpath'];
		$data[$i]['package_description'] = $row['package_description'];

 
    	}
    
    	$result->free();
    	mysqli_close($mysqli);
    	return $data;     
    }
    
    function getMethodManual() {
    	// Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
      
    	$result = $mysqli->query("CALL sp_getMethodsManual()");
    	$data = array();
    	$i=-1;
    
    	while ($row = $result->fetch_assoc()) {
    		++$i;
    		$data[$i]['framework_name'] = trim($row['framework_name']);
    		$data[$i]['test_methodname'] = trim($row['test_methodname']);
    		$data[$i]['package_name'] = trim($row['package_name']);
    		$data[$i]['package_driver'] = trim($row['package_driver']);
    		$data[$i]['test_method'] = trim($row['test_method']);
    		$data[$i]['test_description'] = trim($row['test_description']);
    		$data[$i]['test_example'] = trim($row['test_example']);
    	}
    
    	$result->free();
    	mysqli_close($mysqli);
    	return $data;
    }



  
function updateMethodFilename($packagename,$methodname,$filename) {
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
	
	if (!$mysqli) {
		die ("Could not connect: " . mysqli_error());
	} else {
		#$sql = "UPDATE Testlibrary SET test_description='$description',test_example='$example' WHERE test_id='$id'";
		
		$sql = "update Testlibrary set test_driverfile= '$filename' where test_methodname = '$methodname' and package_id = (select package_id from Packages where package_name = '$packagename')";
		print $sql;
		echo $mysqli->query($sql); // update description & example
		mysqli_close($mysqli);
	}
}





} // End User class definition
?> 