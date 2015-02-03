<?
//Date created 06/28/2012
//Author : Snigdha Sivadas (wvpg48)
//Mysql 


require_once '../common/define_properties.php';

class DBmodule {

    var $dbHost,
        $dbUser,
        $dbName,
        $dbPass,
        $dbUserTable;

    // This is the constructor function definition - it's possible to pass
    // it values just like a normal function, but that isn't demonstrated here
    // These variables will be set for each object that is created using this class
    function DBmodule() {
        $this->dbHost = DBHOST;
        $this->dbUser = SQLUSER;
        $this->dbName = DBNAME;
        $this->dbPass = SQLPASS;
    }
    
    
        function setAddFramework($frame) {
    	// Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
        $sql = "insert into Framework(framework_name) values('$frame');";
    	$result = $mysqli->query($sql);
    	if ($result){
			$data = json_encode(array('success'=>true));
		} else {
			$data = json_encode(array('msg'=>'Dupicate entry of framework is not allowed  ..'));
		}
    
    	
    	mysqli_close($mysqli);
    	return $data;
    }
    
    function setupdateAddFramework($fid,$frame) {
    	// Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
    	$sql = "update Framework set framework_name ='$frame' where framework_id = '$fid';";
    	$result = $mysqli->query($sql);
    	if ($result){
    		$data = json_encode(array('success'=>true));
    	} else {
    		$data = json_encode(array('msg'=>'Dupicate entry of framework is not allowed  ..'));
    	}
       	mysqli_close($mysqli);
    	return $data;
    }
    
    
    function setDeleteAddFramework($fid) {
    // Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
	    $sql = "delete from  Framework where framework_id = '$fid';";
	    $result = $mysqli->query($sql);
	    if ($result){
	    $data = json_encode(array('success'=>true));
	    } else {
	        		$data = json_encode(array('msg'=>'Could not be deleted'));
	        	}
	           	mysqli_close($mysqli);
	    return $data;
	  }
    
    function getFrameworkAdmin() {
    	// Connect to database
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
    	$result = $mysqli->query("CALL sp_getFramework()");
    	$data = array();
       	while ($row = $result->fetch_assoc()) {
    		array_push($data, $row);
    		
    	}
       	$result->free();
    	mysqli_close($mysqli);
    	return $data;
    
    }
    
    
    function getMethodsAdmin($offset, $rs,$coreid,$p1,$p2) {
    	
    	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
    	
    	$wherec = " and (t.test_methodname like '%$p2%' and p.package_name like '%$p1%')";
    	
    	$sql1 = "SELECT test_id,p.package_id, p.package_name,t.test_methodname,t.test_method,t.showflag,t.test_description,t.test_example FROM Packages p inner join Testlibrary t on p.package_id = t.package_id inner join Permission pm on p.package_id =  pm.package_id where pm.permission_access IN ('-1','$coreid') ".$wherec;
    	$rs1 = $mysqli->query($sql1);
    	
	    $sql = "SELECT test_id,p.package_id, p.package_name,t.test_methodname,t.test_method,t.showflag,t.test_description,t.test_example FROM Packages p inner join Testlibrary t on p.package_id = t.package_id inner join Permission pm on p.package_id =  pm.package_id where pm.permission_access IN ('-1','$coreid') ".$wherec."   limit ". $offset." , ".$rs;
	    $rs = $mysqli->query($sql);
	   
	    $result["total"] = $rs1->num_rows;
	    
        $rows = array();
       	while ($row = $rs->fetch_assoc()) {
       		
    		array_push($rows, $row);
    		
    	}
    	$result["rows"] = $rows;
    	$rs->free();
    	mysqli_close($mysqli);
	    return $result;
     }
    
     function setAddTestLibrary($packn,$mname,$method,$desc,$exam) {
     	// Connect to database
     	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
     	$result = $mysqli->query("CALL sp_insertTestLibrary('$packn','$mname','$method','$desc','$exam')");
     	if ($result){
     		$data = json_encode(array('success'=>true));
     	} else {
     		$data = json_encode(array('msg'=>'Invalid package name....'));
     	}
     
     	 
     	mysqli_close($mysqli);
     	return $data;
     }
     
     
     function setUpdateTestLibrary($tid,$packn,$mname,$method,$showf,$desc,$exam) {
     	// Connect to database
     	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
     	
     	$result = $mysqli->query("CALL sp_insertTestLibraryAdmin($tid,'$packn','$mname','$method','$showf','$desc','$exam')");
     	if ($result){
     		$data = json_encode(array('success'=>true));
     	} else {
     		$data = json_encode(array('msg'=>'Invalid package name....'.$tid." ====  ==== ".$packn.$showf));
     	}
     	 
     	 
     	mysqli_close($mysqli);
     	return $data;
     }
     
     function getPackagesBox($framework,$coreid) {
     	
     	$mysqli = new MySQLI($this->dbHost,$this->dbUser,$this->dbPass,$this->dbName);
     	$sql = "SELECT p.package_id, p.package_name FROM Packages p inner join Permission pm on p.package_id =  pm.package_id where pm.permission_access IN ('-1','$coreid')";
        $result = $mysqli->query($sql);
     	    
        $data = array();
        while ($row = $result->fetch_assoc()) {
        array_push($data, $row);
     
        }
        return $data;
     }
    
} // End User class definition
?> 