<?php
//$cycleplan=$GET['cycleplan'];
$username=$_GET['user_name'];
//$username="prx384";
$username=$username."_";
$runlist_file=$_GET['cycleplan'];
$runlist_file=$runlist_file.".xml";
$runlist_file="/datafiles/runlistfiles/".$username.$runlist_file;

//$runlist_file_name=$_GET['file_path_1'];
$runlist_file_contents=$_GET['runlist_file_1'];
$runlist_file_contents=$runlist_file_contents.PHP_EOL;
//$runlist_file="/home/bluremployee/runlist_dir/".$_GET['file_name'];
$fh=fopen($runlist_file,'a') or die("cant open file");
$write_status=fwrite($fh,$runlist_file_contents);
if($write_status)
{
  echo 1;
}
else 
{
echo 0;
}
fclose($fh);



?>
