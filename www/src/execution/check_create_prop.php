<?php

$filename = $_REQUEST['filename'];
//$filename = "0A3BC2B50B020014.json";

$file = "/datafiles/propertyfiles/" . $filename;
$default_file = "../../tempdata/property_keys.txt";
$string2 = file_get_contents($default_file);
$string2 = chop($string2);
$json_d = split("\n", $string2);
$count2 = count($json_d);



//Initiate /tempdata/property_data dir

$prop_dir = "../../tempdata/property_data";
if(!is_dir($prop_dir)){
	$make_dir = "mkdir " . $prop_dir;
	exec($make_dir);
}


// If file is not exist, initiate the file



if(!file_exists($file)){
	$parray = array();
	for ($n = 0; $n < $count2; $n++){
		$parray[$n]["name"] = $json_d[$n];
		$parray[$n]["value"] = "";
		$parray[$n]["editor"] = "text";
	}
	$fp = fopen($file, 'w');
        fwrite($fp, json_encode($parray));
        fclose($fp);
}else{
	$string = file_get_contents($file);
	$json_a = json_decode($string, true);
	$count = count($json_a);

	$add_array = array();

	for ($i=0; $i < $count2 ; $i++){
		$flag = 0;
		for ($j=0 ; $j < $count ; $j++){
			if ($json_a[$j]["name"] == $json_d[$i]) $flag =1;
		}
		if ($flag == 0 ) array_push($add_array, $json_d[$i]);
	}

	$count3 = count($add_array);
	for($k = 0 ; $k < $count3 ; $k++){
		$n = $count + $k;
		$json_a[$n]["name"] = $add_array[$k];
		$json_a[$n]["value"] = "";
		$json_a[$n]["editor"] = "text";
	}

	$fp = fopen($file, 'w');
	fwrite($fp, json_encode($json_a));
	fclose($fp);
}


$file2 = "../../tempdata/property_data/" . $filename;
$cmd = "rm -f $file2";
exec($cmd);

$cmd = "cp $file $file2";
exec($cmd);

$string = file_get_contents($file2);
$json_a = json_decode($string, true);

echo json_encode($json_a);
?>
