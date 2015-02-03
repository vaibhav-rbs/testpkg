<?php

$rstring = $_REQUEST['rstring'];

$rstring = substr($rstring, 3, strlen($rstring) -7);
$file = array();
$file = split("</p><p>", $rstring);
$count = count($file);

$source_dir = "/datafiles/testscriptjson";

for($i = 0; $i < $count; $i++){
	$f1 = $source_dir . "/" . $file[$i] . ".json";
	$f2 = $source_dir . "/" . $file[$i] . "_affected.json";
	$cmd = "mv '$f1' '$f2'";
	exec($cmd);
	$cmd = "chmod 777 '$f2'";
	exec($cmd);
}





echo "0";
?>
