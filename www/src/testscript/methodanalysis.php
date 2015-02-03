<?php 
//Created by Snigdha Sivadas wvpg48
//Description : Class to analysis the method and create Json 
//created Dec 21 2001
require_once 'testscriptcommon.php';


class MethodAnalysis{
	
	private $test_methodname,$package_name,$package_driver,$test_method,$test_description,$test_count,$methodsstr;
	private $json_filename;
	private $group; 
	
	
	
	function MethodAnalysis($sqldata) {
		$this->test_methodname = trim($sqldata['test_methodname']);
		$this->package_name = trim($sqldata['package_name']);
		$this->package_driver = trim($sqldata['package_driver']);
		$this->test_method = trim($sqldata['test_method']);
		$this->test_description = trim($sqldata['test_description']);
	}
	
	
	public function setTestMethodString($mstring) {
		$this->methodsstr = $mstring;
	}
	
	public function getTestMethodname() {
		return $this->test_methodname ; 
	}
 
	public function getPackagename() {
		return $this->package_name ;
	}
	
	public function getPackagedriver() {
		return $this->package_driver;
	}
	
	public function getTestmethod() {
		return $this->test_method;
	}
	
	public function getTestDescription() {
		return $this->test_description;
	}
	
	public function getTestMethodString() {
		return $this->methodsstr;
	}
	
	public function getMethodIndex(){
		$str = $this->getTestMethodString();
		$pieces = explode(",", $str);
		//$count = 0 ;
		
		/*foreach ($data as $pieces) {
			
			if (substr_count($data,$this->package_name.'__'.$this->test_methodname)== 1)
				$count = $count++;
			
		}*/
		
		return count($pieces)-1;
	}

	public function getGroup(){
		//$this->group = $this->package_name.'__'.$this->test_methodname;
		$this->group = $this->package_name.'__'.$this->test_methodname.'__'.$this->getMethodIndex();
		return $this->group;
	}
	
	public function printData() {
		return " Method Analysis ".$this->test_methodname." : ".$this->package_name. " : " .$this->package_driver;
	}
	
	public function getJsonFilename(){
		$mn = $this->test_methodname;
		$pn = $this->package_name;
		$str = preg_replace('/\./', '_',trim($pn));
		$json_filename =   $str.'__'.$mn.'.json';
		return $json_filename;
	}
	
	public function constructJson(){
		
		return '['.$this->getPackagestr().$this->getConfigurationstr().$this->getParamsstrpropertygrid().']';
		
	}
	
	
	public function removeCharactersMethod(){
		
		$pre_process= $this->test_method;
		$pre_process = trim($pre_process);
		$pre_process= preg_replace('/<.+?>/', ' ',$pre_process);
		$pre_process= preg_replace('/&nbsp;/', ' ',$pre_process);
		$pre_process= preg_replace('/&lt;.+?&gt;/', ' ',$pre_process);
		$pre_process= preg_replace('/\n/', ' ',$pre_process);
		
		return $pre_process;
	}
	
	public function gettimeSpinnerstr(){
		$timesp = '{"type":"timespinner","options":{"showSeconds":"true","highlight":2,"editable":false}}';
		return $timesp;
			
	}
	
	public function getNumberstr(){
		//$numsp = '{"type":"numberspinner","options":{"min":0,"value":"0","max":100,"increment":1,"editable":false}}';
		$numsp = '{"type":"numberspinner","options":{"min":0,"value":"0","max":1000,"increment":1}}';
		return $numsp;
	}
	
	
	public function getConfigurationstr(){
		$timesp = $this->gettimeSpinnerstr();
		$numsp = $this->getNumberstr();
		$key = $this->getGroup();
		$temp = "";
		/*$temp = $temp.'{"name":"delay","value":"","group":'.'"'.$key.'","editor":'.$timesp.'},';
		$temp = $temp.'{"name":"duration","value":"","group":'.'"'.$key.'","editor":'.$timesp.'},';
		$temp = $temp.'{"name":"count","value":"","group":'.'"'.$key.'","editor":'.$numsp.'}';*/
		$temp = $temp.'{"name":"'.DELAYNAME.'","value":"","group":'.'"'.$key.'","editor":'.$timesp.'},';
		$temp = $temp.'{"name":"'.DURATIONNAME.'","value":"","group":'.'"'.$key.'","editor":'.$timesp.'},';
		$temp = $temp.'{"name":"'.COUNTNAME.'","value":"","group":'.'"'.$key.'","editor":'.$numsp.'}';
		return $temp;	
	}
	
	public function getPackagestr(){
		
		 $key = $this->getGroup();
		 $pname = $this->getPackagename();
		 $str1 = '{"name":"package","value":'.'"'.$pname.'","group":'.'"'.$key.'"},';
		// echo "dsfdsfdsfdsf".$str1;
		return $str1;
	}
	
	public function getParamsXMLtoJson(){
		$method = false;
		$data = $this->getParams();
		$group ="";
		$params = array();
		$array = array();
	
		foreach ($data as $param) {
			$param =trim($param);
	
			if(trim($param) == $this->test_methodname){
				$group = $this->getGroup();
				$method = true;
			}
			else{
				$pvalue = $this->getTypeMethod($param,"j");
	
				if(strlen(trim($pvalue[0]))>0){
					// echo "\n returned val : ".$pvalue[0];
					array_push($array, $pvalue);
				}
	
			}
		}
	
		return $array;
	}
	
	
	public function getParamsXML(){
		$method = false;
		$data = $this->getParams();
		$group ="";
		$params = array();
		$array = array();
		
		foreach ($data as $param) {
			$param =trim($param);
				
			if(trim($param) == $this->test_methodname){
				$group = $this->getGroup();
				$method = true;
			}
			else{
				$pvalue = $this->getTypeMethod($param,"x");
				
				if(strlen(trim($pvalue[0]))>0){
				 // echo "\n returned val : ".$pvalue[0];
				  array_push($array, $pvalue);
				}
				
			}
		}
		
		return $array;
	}
	
	public function getParamsstrpropertygrid(){
		$method = false;
		$data = $this->getParams();
		$group ="";
		$tempstr = "";
		
		foreach ($data as $param) {
			$param =trim($param);
			
             if(trim($param) == $this->test_methodname){
             	$group = $this->getGroup();
             	$method = true;
             }
             else{
             	$pvalue = $this->getTypeMethod($param,"p");
             	foreach ($pvalue as $key) {
             		if(strlen(trim($key))>0){
		             	//$str = ',{"name":"'.$key.'","value":"","group":'.'"'.$group.'","editor":"text"}';
		             	$str = ',{"name":"'.$this->getParam($key).'","value":"","group":'.'"'.$group.'","editor":"text"}';
		             	$tempstr = $tempstr.$str;
             		}
             	}
             	
               	
             }
		}
		
		return $tempstr;
	}
	
	
	public function getParams(){
		
		$str = $this->removeCharactersMethod();
		$properties = preg_split('/\,|\)|\(/',$str);
		return $properties;
		
	}
	
	
	public function getTypeMethod($val,$type){
		$properties = preg_split('/\]|\[/',$val);
		$output = array();
		
	   switch($type){
	   	
	   	case "p":
			if(count($properties) == 1 ) $output[0] =  $properties[0];
	   		
			else if(count($properties) > 1 ){
				$properties1 = preg_split('/:/',$properties[1]);
				$output = $properties1;
			}
			break;
	   	case "x":
	   		  if(count($properties) == 1 ){
	   		  	$output[0] =  $properties[0];
	   		  	$output[1]=array();
	   		   }
	   		  else if(count($properties) > 1 ){
		   		    $output[0] =  $properties[0];
					$properties1 = preg_split('/:/',$properties[1]);
					$output[1] = $properties1;
			  }
			 
	   		break;
	   		case "j":
	   			if(count($properties) == 1 ){
	   				$output[0] =  $this->getParam($properties[0]);
	   				$output[1]=array();
	   			}
	   			else if(count($properties) > 1 ){
	   				$output[0] =  $this->getParam($properties[0]);
	   				$properties1 = preg_split('/:/',$properties[1]);
	   				$output[1] = $properties1;
	   			}
	   		
	   			break;
	   			
	   	default:
	   		break;
	 	
	   }
	   return $output;
	}
	
	function getParam($ky){
	
		$kyit = preg_split("/[\s]+/", $ky);
		$len = count($kyit);
		if ($len > 1 )return $kyit[1];
		else return $kyit[0];
	
	}
	
	
	 
	
}




?>