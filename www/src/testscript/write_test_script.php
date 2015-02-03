<?php

//Author : Snigdha Sivadas (wvpg48)
//Writetestscript to xml

 require_once '../common/define_properties.php'; 
 require_once 'testscriptcommon.php';
 
 include('testscriptdb.php');
 include('methodanalysis.php');

$test_id=$_GET['testid'];
$test_desc=$_GET['testdesc'];
$groupname=$_GET['groupname1'];
$test_file=$_GET['testfilename'];
$datajson=$_GET['datajson1'];
$respo=$_GET['respo'];
$startflag = $_GET['startflag'];
$groupindex = $_GET['groupindex'];

//$file_path = OUTSCRIPTS.$test_id.".xml";
$testscript_content="";
$file_path=OUTSCRIPTS.getCharactersConvert($test_id).".xml";


$classl = ".LogicalMethods";
chmod($file_path, 0777);
system ('chmod 777 '.$file_path);

if(!$fh=fopen($file_path,'a'))
{
  echo "FALSE";
  exit;

}

if($startflag=="false")
	$testscript_content=$testscript_content.'<invaderPlus:runlist xmlns:invaderPlus = "com.motorola.wireless.qa.invaderPlus">';
	//$groupindex=0;


$decode_array1=json_decode($datajson);
// Convert array to object and then object back to array
$decode_array = objectToArray($decode_array1);
$handle=fopen($file_path,'a') or die("cant open file");
$package_name="";
$delays="";
$testscript_param="";
$counts="";
$durations="";
$indexflag = false;




//echo "\n decodedarray length   = ".count($decode_array);

foreach( $decode_array as $key => $value){
	$key = trim(deriveNameXML($key));
	if($key=="package"){
		$package_name=$value;
		//echo "package_name loop ".$package_name;
	 }
	else if($key=="delay"){
		if(strlen($value.trim('')) > 0) $delays= ' delay='.'"'.converttimespinner($value).'" ';
		else $delays ="";
	}
	else if($key=="duration"){
		if(strlen($value.trim('')) > 0) $durations= ' duration='.'"'.converttimespinner($value).'" ';
		else $durations ="";
	}
	else if($key=="count"){
		if(strlen($value.trim('')) > 0) $counts= ' count='.'"'.$value.'" ';
		else $counts ="";
	} 
	
	/*else {
		//$testscript_param = $testscript_param.'<param name='.'"'.$key.'"'.'>'.$value.'</param>';
		$valp = getParamType($key);
		$typep="";
		if(strlen($valp)> 0 )
			$typep = ' type='.'"'.$valp.'" ';
	    else $typep="";
			
		
		$testscript_param = $testscript_param.'<param name='.'"'.getParam($key).'" '.$typep.' >'.$value.'</param>';
	 	//echo $testscript_param;
	}*/

}
$testmodule = new Testmodule();
$methodname = getMethod($groupname);
//echo "package_name".$package_name;
$data = $testmodule->getMethodDetails($package_name,$methodname);
$methodanalysis =  new MethodAnalysis($data[0]);
//echo $methodanalysis->printData();
$ma_params = $methodanalysis->getParamsXML();

foreach( $ma_params as $arr){
	 $key = $arr[0];
	 $value = "";
	 $valp = getParamType($key);
	 $typep="";
	 if(strlen($valp)> 0 )
	 	$typep = ' type='.'"'.$valp.'" ';
	 else $typep="";
	 
	  if(count($arr[1])>0){
		foreach($arr[1] as $val){
			$tmp = getIllegal($decode_array[$val]);
			$value = $value.$tmp.'^';
		}
		$value = substr($value, 0, -1);
	  }
	  else{
	  	$value = getIllegal($decode_array[getParam($key)]);
	  }
	   // $testscript_param = $testscript_param.'<param name='.'"'.getParam($key).'" '.$typep.'>'.$value.'</param>';
	  $testscript_param = $testscript_param.'<param name='.'"'.getParam($key).'" '.$typep.'>'.'"'.$value.'"'.'</param>';
}


if ($indexflag == false)
	$indexs= ' index ='.'"'.$groupindex.'" ';

$userdef= $delays.$durations.$counts.$indexs;

$testscript_content=$testscript_content.'<test class="'.$package_name.$classl.'"'.'  method='.'"'.trim($methodname).'"'.'  id='.'"'.getIllegal($test_id).'"'.' des="'.$test_desc.'" '.$userdef.'>';
$testscript_content=$testscript_content.$testscript_param;
$testscript_content = $testscript_content.'</test>';

if($respo=="true"){
$testscript_content = $testscript_content.'</invaderPlus:runlist>';}

if(fwrite($handle,$testscript_content) === FALSE){
  echo "FALSE";
  exit;
} else echo "TRUE";
  // echo "test  >>>>>>>>".$testscript_content;

//fwrite($handle, $testscript_content);
fclose($fh);


function objectToArray($d) {
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(__FUNCTION__, $d);
		}
		else {
			// Return array
			return $d;
		}
	}
 
function getMethod($ky){
	
	$kyit = preg_split("/__/", $ky);
	$len = count($kyit);
	if ($len > 1 ) 
	 return $kyit[1];
}	
function getParam($ky){
	
	$kyit = preg_split("/[\s]+/", $ky);
	$len = count($kyit);
	if ($len > 1 )return $kyit[1];
	else return $kyit[0];
	
}

function getParamType($ky){

	$kyit = preg_split("/[\s]+/", $ky);
	$len = count($kyit);
	if ($len > 1 ){
	   //	echo "kyit".$kyit[0];
	   	return $kyit[0];
	 }
	else return "";

}

function getIllegal($ky){
	$ky= preg_replace('/\&/', '&amp;',$ky);
	$ky= preg_replace('/\</', '&lt;',$ky);
	$ky= preg_replace('/\>/', '&gt;',$ky);
	//$ky= preg_replace('/\"/', '&quot;',$ky);
	
	
	return $ky;
}

function converttimespinner($timeh){

	$output = preg_split("/:/",$timeh);
	$result = "";
	//echo PHP_EOL."count(output)=".count($output)."    ".$timeh;
	if(count($output)== 3){
	    $result = $output[0].'h'.$output[1].'m'.$output[2].'s';
	    return $result;
	}
	else  return $timeh;	
}

?>
