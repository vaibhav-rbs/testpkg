<?php

function app_log_line($string){
	$dtime = date("m-d-y H:i:s");
	$target_str = $dtime . "::" . $string . "\n";
	$fp = fopen('/datafiles/logfiles/application.log', 'a');
	fwrite($fp, $target_str);
	fclose($fp);
}




?>
