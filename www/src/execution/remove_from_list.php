<?php

$execlist = $_REQUEST['execlist'];
$execlist = chop($execlist);
//$execlist = "results_0A3BC2B50B020014_0A3BC2B50B020014_crmg76_demo_product - (MD Advance Platforms) Cycle 1.xml_2012_04_23_16_48_59";


// get user_name

$tmp_arr = array();
$tmp_arr = split("_" , $execlist);
$user_name = $tmp_arr[3];


// remove this entry from tc_ready/$user_name file

$tc_txt = "/datafiles/logfiles/tc_ready/" . $user_name;
$tmp_txt = "/tmp/tc_ready_" . $user_name;
$infile=fopen($tc_txt,"r");
$outfile = fopen($tmp_txt, 'w');
while($line = fgets($infile)){
	$line = chop($line);
        if($line != $execlist){
		fwrite($outfile, $line . "\n");
	}
}
fclose($infile);
fclose($outfile);

$cmd_back_tc_ready = "cp $tmp_txt $tc_txt";
exec($cmd_back_tc_ready);
$cmd_clean = "rm $tmp_txt";
exec($cmd_clean);

echo json_encode(array('msg'=>"success"));

?>
