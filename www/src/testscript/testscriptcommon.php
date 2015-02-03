
<?php
//Author : Snigdha Sivadas (wvpg48)



define("DELAYNAME","<B><I><font color='#E3564F'>How long to wait before run?(Optional)</font></I></B>");
define("DURATIONNAME","<B><I><font color='#E3564F'>How long to run?(Optional)</font></I></B>");
define("COUNTNAME","<B><I><font color='#E3564F'>How many times to run?(Optional)</font></I></B>");

function getCharactersConvert($ky){
	$ky= preg_replace('/%26/', '\&',$ky);
	#$ky= preg_replace('/%20/', ' ',$ky);
	return $ky;
}


function getDateTime(){
	return date("m/d/y");		
}


function deriveNameXML($str){
	
	if (strcmp($str, DELAYNAME)==0) return  "delay" ;
	
	else if (strcmp($str, DURATIONNAME)==0) return "duration" ;
	
	else if (strcmp($str, COUNTNAME)==0) return  "count" ;
	
	else return $str;
}

//echo deriveNameXML("count -> How many times to run?");
?>