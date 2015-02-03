<?php
/*
 * Write test script XML to temporary XML file so that Script Viewer can view the xml scripts.
 * Jung Soo Kim
 * April 25, 12
 */
$filename = $_GET['filename'];
$filename = "/datafiles/testscriptfiles/" . $filename . ".xml"; // add path

if($read = fopen($filename, 'r')){
	//read the content
	$content = fread($read, filesize($filename));
	fclose($read);
	
	// write to TEMP test script XML file
	if($write = fopen("../../tempdata/testscript.xml", 'w+')){
		if(fwrite($write, $content) === FALSE){
			echo error_log("Cannot write TEMP test script XML file");
		} else {
			fclose($write);
			echo "SUCCESS";
		}
	} else {
		echo error_log("Cannot open TEMP test script XML file");
	}
} else {
	echo error_log("Cannot open $filename");
}
?>