<?php
//$cycleplan=$GET['cycleplan'];
$username=$_GET['user_name'];
//$username="prx384";
$username=$username."_";
$runlist_file=$_GET['cycleplan'];
$runlist_file=$runlist_file.".xml";
$runlist_file="/datafiles/runlistfiles/".$username.$runlist_file;

$fh=fopen($runlist_file,'w') or die("cant open file");

fclose($fh);
