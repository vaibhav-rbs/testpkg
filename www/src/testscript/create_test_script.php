<?php
	require_once '../common/define_properties.php'; 
	require_once 'testscriptcommon.php';
	$test_id1=$_GET['testfilename'];
	
	$test_id=$_GET['testid'];
	$file_path=OUTSCRIPTS.getCharactersConvert($test_id).".xml";
	//$file_path=OUTSCRIPTS.$test_id."xml";
	echo $file_path;
	
	if(!$handle = fopen($file_path, 'w'))
	{
	  echo "FALSE";
	  exit;
	
	}
	
	fwrite($handle,"");
	fclose($handle);
	system ('chmod 777 '.$file_path);
?>
