<?php
#require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
//Created by snigdha Sivadas
//Temp file to append data for multiple testcases
require_once '../common/define_properties.php';
include('testscriptdb.php');
include('methodanalysis.php');
require_once 'testscriptcommon.php';


	$currentfile = $_GET['xmlfile_name'];
	//$currentfile = 'HSS6%20UPGRADE.Sanity:001-047.xml';
	$path = OUTSCRIPTS;
	$currentfile = getCharactersConvert($currentfile);
	$groupindex = -1;
	
	
	//echo $currentfile;
	
	
	if (!file_exists ($path.$currentfile)){
	  	    echo  "FALSE";
	        exit;
	}
	else { 
			$newXML = file_get_contents($path.$currentfile);
			$arr = Array();
			$arrtcd="";
			$arrtcd1="";
			$newjson = "[";
			$xml = new SimpleXMLElement($newXML);
			parse_recursive($xml);
			if (strlen($arrtcd1)>2)
				$newjson = $newjson.$arrtcd.",".$arrtcd1;
			else $newjson = $newjson.$arrtcd;
			
			$newjson = $newjson."]";
			
			echo $newjson."inxpackage=".$packagestr;
	}
	
	function getIllegalC($ky){
	//	$ky= preg_replace('/\"/', '\"',$ky);
		return $ky;
	}
	
	function parse_recursive(SimpleXMLElement $element, $level = 0)
	{   $len =5;
		global $arrtcd;
		global $arrtcd1;
		$groupset = false;
		$strtest = "";
		$delay = "";
		$count = "";
		$duration = "";
		$package = "";
		global $packagestr;
		global $group;
		global $ma_params;
		global $groupindex;
	    
		
		
		$editor = '"editor":"text"';
		$dedit = '"editor":{"type":"timespinner","options":{"showSeconds":"true","highlight":2,"editable":false}}';
		//$numsp = '"editor":{"type":"numberspinner","options":{"min":0,"value":"0","max":100,"increment":1,"editable":false}}';
		$numsp = '"editor":{"type":"numberspinner","options":{"min":0,"value":0,"max":1000,"increment":1}}';
		
		$defaultdelay = '"name":'.'"'.DELAYNAME.'"'.' , '.'"value":"'."".'", '.$dedit.",";
		$defaultcount = '"name":'.'"'.DURATIONNAME.'"'.' , '.'"value":"'."".'", '.$numsp.",";
		$defaultduration = '"name":'.'"'.COUNTNAME.'"'.' , '.'"value":"'."".'", '.$dedit.",";
		
		
		 
		$indent     = str_repeat("\t", $level); // determine how much we'll indent
		$value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
		$attributes = $element->attributes();   // get all attributes
		$children   = $element->children();     // get all children
	
		// only show attributes if there are any
		if((count($attributes) > 0)&&($element->getName()=="test"))
		{     $group = "";
			  $methodattr = "";
			  $spac="";
			  $pac="";
			//echo $indent.'Has '.count($attributes).' attribute(s):'.PHP_EOL;
			foreach($attributes as $attribute)
			{
				$str = "";
				 $attrtemp = deriveNameXML($attribute->getName());
				//echo "{$indent}- {$attribute->getName()}: {$attribute}".PHP_EOL;
				switch ($attrtemp){
					
					case "class":
						$att = $attribute;
						
						$pie = explode('.', $att);
						$pac= "";
						
						$len=count($pie);
						 $i = 0;
						while ($i < count($pie)-1)
						{
							if($i==0){
								$pac = $pac.$pie[$i];
								$spac= $spac.$pie[$i];
							}
							else{
								$pac = $pac.".".$pie[$i];
								$spac= $spac."_".$pie[$i];
							}
							
							$i++;
						}
						
						$spac = $pac;
						
						$package = '"name":'.'"'."package".'"'.' , '.'"value":"'."{$pac}".'", ';
						
						break;
					case "method":
						$methodattr = $attribute;
						//$group = '"group":"'."{$pac}".'__'."{$attribute}".'"  ';
						$groupindex = ++$groupindex;
						$group = '"group":"'."{$pac}".'__'."{$attribute}".'__'."{$groupindex}".'"  ';
						$groupset = true;
						// echo $str;
						break;
					case "delay":
						//$dedit = '"editor":{"type":"timespinner","options":{"showSeconds":"true"}}';
						$datt =  preg_replace('/h|m/', ':', $attribute);
						$datt =  preg_replace('/s/', '', $datt);
						$delay = '"name":'.'"'.DELAYNAME.'"'.' , '.'"value":"'."{$datt}".'", '.$dedit.",";
						//echo $str;
						break;
					case "count":
						//$numsp = '"editor":{"type":"numberspinner","options":{"min":0,"value":"0","max":100,"increment":1,"editable":"true"}}';
						$count = '"name":'.'"'.COUNTNAME.'"'.' , '.'"value":"'."{$attribute}".'", '.$numsp.",";
						//echo $str;
						break;
					case "duration":
						//$dedit = '"editor":{"type":"timespinner","options":{"showSeconds":"true"}}';
						$datt =  preg_replace('/h|m/', ':', $attribute);
						$datt =  preg_replace('/s/', '', $datt);
						$duration = '"name":'.'"'.DURATIONNAME.'"'.' , '.'"value":"'."{$datt}".'", '.$dedit.",";
						//echo $str;
						break;
					
					default:
						break;
				}						
				
			}
	
			
			
			if($groupset){
				
				
				
				if (strlen($package)>$len)
					$str = "{".$package.$group."}";
				if (strlen($delay)>$len)
					$str = $str.",{".$delay.$group."}";
				else 
					$str = $str.",{".$defaultdelay.$group."}";
				
				if (strlen($duration)>$len)
				$str = $str.",{".$duration.$group."}";
				else
				$str = $str.",{".$defaultduration.$group."}";
				
				if (strlen($count)>$len)
					$str = $str.",{".$count.$group."}";
				else
					$str = $str.",{".$defaultcount.$group."}";
				
				
				
				if(strlen($arrtcd)>2)
					$arrtcd = $arrtcd.",".$str;
				else
					$arrtcd = $arrtcd.$str;
				
				if(strlen($packagestr)>2)
						$packagestr = $packagestr.",".$spac."__".$methodattr."__".$groupindex;
						//$packagestr = $packagestr.",".$spac."__".$methodattr.".json";
				else
						//$packagestr = $packagestr.$spac."__".$methodattr.".json";
						$packagestr = $packagestr.$spac."__".$methodattr."__".$groupindex;
				   
			}
			
			$testmodule = new Testmodule();
			$data = $testmodule->getMethodDetails($pac,$methodattr);
			$methodanalysis =  new MethodAnalysis($data[0]);
			//echo $methodanalysis->printData();
			$ma_params = $methodanalysis->getParamsXMLtoJson();
			
				
		}
			
	
		
		//echo "{$indent}Parsing '{$element->getName()}'...".PHP_EOL;
		//if(count($children) == 0 && !empty($value)) // only show value if there is any and if there aren't any children
		if(count($children) == 0)
		{	
			$str="";
			$valset =false;
			//echo "{$indent}Value: {$element}".PHP_EOL;
			switch ($element->getName()) {
				case "param":
					$attr="";
					$attr1="";
					$attrval="";
					$arrval = array();
					$flagd = false;
					$match = false;
					$attributes1 = $element->attributes();
					if (count($attributes1)>0){
						foreach($attributes1 as $attribute){
							switch ($attribute->getName()){
								case "name":
									$attr = $element['name'];
									$attrval = $element;
									foreach( $ma_params as $arr){
										//echo "check for equal   -> ".$arr[0]."==".$attr;
										if ($arr[0]==$attr){
											$match = true;
											$lena = count($arr[1]);
											if($lena > 0){
												$flagd = false;
												$i = -1;
												$attrval = trim($attrval);
												$attrval = trim($attrval,'"');
												//$arrval=preg_split('/\,/',$attrval);
												$arrval=preg_split('/\^/',$attrval);
												$arrkey = $arr[1];
												while($i< $lena-1){
													++$i;
													$name1 = $arrkey[$i];
													$val1 = getIllegalC($arrval[$i]);
													$str1 = '"'."name".'":"'."{$name1}".'"  ,'.'"'."value".'":"'."{$val1}".'" , '.$editor;
													$str2 = "{".$str1.','.$group."},";
													$str = $str.$str2;
													}		
												$str = substr($str, 0, -1);
											}
											else {$flagd = true; }														
										}

										if($match)break;
									}
									//$str = '"'."name".'":"'."{$attr}".'"  ,'.'"'."value".'":"'."{$element}".'" , '.$editor;
									//$str = "{".$str.','.$group."}";
									//echo "{$indent}Attribute: {$element['name']}".PHP_EOL;
									//echo "{$indent}Value: {$element}".PHP_EOL;
									break;
								case "type":
									$attr1 = $element['type'];
			  						break;
								default:
									break;
							}
						}
						if($flagd==true){
							$attrval = trim($attrval);
							$attrval =  getIllegalC($attrval);
							$attrval = trim($attrval,'"');
							$str = '"'."name".'":"'."{$attr}".'"  ,'.'"'."value".'":"'."{$attrval}".'" , '.$editor;
						 /*  
						   if(strlen($attr1)>0){
								$str = '"'."name".'":"'."{$attr1}".' '."{$attr}".'"  ,'.'"'."value".'":"'."{$attrval}".'" , '.$editor;
							}
							else
								$str = '"'."name".'":"'."{$attr}".'"  ,'.'"'."value".'":"'."{$attrval}".'" , '.$editor; */
								
							$str = "{".$str.','.$group."}";
						}
						
							
						if(strlen($arrtcd1)> 5 )
						      $arrtcd1 = $arrtcd1.",".$str;
						else
							  $arrtcd1 = $arrtcd1.$str;
					}
						
				break;
				default:
					//echo "i is not equal to 0, 1 or 2";
				break;
			}						
		}
	
		// only show children if there are any
		if(count($children))
		{
			//echo $indent.'Has '.count($children).' child(ren):'.PHP_EOL;
			foreach($children as $child)
			{
				parse_recursive($child, $level+1); // recursion :)
			}
		}
		//	echo $indent.PHP_EOL; // just to make it "cleaner"
	}
	
?>