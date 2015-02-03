	<?php
	//Created by Snigdha Sivadas wvpg48
	require_once '../common/define_properties.php'; 
	require_once 'testscriptcommon.php';
	
	
	$testfilename=$_GET['xmlfilename'];
	$testfilename = getCharactersConvert($testfilename);
	$tf = str_replace(" ", "\ ", $testfilename);
	$tf = str_replace("&", "\&", $tf);
		  
	$file = OUTSCRIPTS.$tf;
	$cmd = 'rm '.$file;
	 
	system($cmd);
	
	?>
