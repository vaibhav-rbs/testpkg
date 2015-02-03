<?php

$username = $_REQUEST['username'];
$package  = $_REQUEST['pname'];
$method  = $_REQUEST['mname'];
$parameters  = $_REQUEST['parameters'];

$save_dir = "/datafiles/logfiles/parameter_change";
$file = $save_dir . "/" . $username . "_" . $package . "_" . $method;
if(file_exists($file)){
	$string = file_get_contents($file);
	$parameters0 = json_decode($string, true);
}

$flag = 0;

$count0 = count($parameters0);
$count = count($parameters);

if($count != $count0){
	$flag = 1;
}else{
	for($i=0; $i < $count ; $i++){
		if(count(array_diff($parameters0[$i],$parameters[$i])) || count(array_diff($parameters0[$i],$parameters[$i]))){
			$flag = 1;
			break;
		}
	}
}


$cmd = "rm " . $file;
$ret = exec($cmd);

if($flag == 1){
	//$source_dir = "/datafiles/testscriptjson";
	$source_dir = "/datafiles/testscriptjson";
	if ($dh = opendir($source_dir)) {
		$rstring = "";
		while (($jfile = readdir($dh)) !== false) {
                        if (!is_dir($jfile)){
			   $pos3 = strpos($jfile, "affected");
			   if($pos3 === false){
				$jstr = file_get_contents($source_dir . "/" . $jfile);
                                $pos = strpos($jstr, $package);
                                if ($pos === false){
				}else{
					$pos2 = strpos($jstr, $method);
					if ($pos2 === false){
					}else{
						$f1 = $source_dir . "/" . $jfile;
						list($name, $left) = split(".json", $jfile);
						$rstring = $rstring . "<p>" . $name . "</p>";
					}
                                }
			   }
                        }
                 }
	}

}else{
	$rstring = "";
}


echo $rstring;
?>
