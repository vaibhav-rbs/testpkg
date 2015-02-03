<?php

//Created by Snigdha Sivadas wvpg48
//Description : To load the testcasedeatils from the test case name

require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP
include('../common/tc_functions.php');  



$testcasename=$_GET['test_case'];
//$testcasename="Android Features.Feature 31298 31282:015-053";
//$testcasename="Android Features.Feature 26264 28361 25741:008-004";
$results2 = Get_Test_CaseDetails_By_TestCase($testcasename);
$results2 = fix_desc_format($results2);
$tmp = "";
//$xml2= simplexml_load_string(trim($results2));
$arr = Array();
$arrtcd = Array();
$table = Array();
$xml = new SimpleXMLElement($results2);
$children   = $xml->children();
parse_recursive($xml);
$arrtcd['table'] = $table;
$arr = $arrtcd;
echo json_encode($arr);


// To fix broken of test description if contains <XXX>.  Added by Jing-May
function fix_desc_format($input){

	list($session1, $str) = split ("<CaseDescription>", $input);
	list($str, $session3) = split ("</CaseDescription>", $str);
	$pos = strpos($str,"<");
	if($pos === false) {
                          // not found in string
        }else{
		$str = str_replace("<", "[", $str);
		$str = str_replace(">", "]", $str);
	}
	$output = $session1 . "<CaseDescription>" . $str . "</CaseDescription>" . $session3;

	return $output;
}										

function filter_data($text)
{   $text = $text.trim('');
  //  $text=preg_replace("/[^a-z \d : . ( ) \/\/ { }  \/n \/t \/s]*/i", "", $text);
    $text=preg_replace("/[^a-z \d : . ( ) \/\/ { } \/s]*/i", "", $text);
    $newchar = $text;
    return $newchar;
	
}



function parse_recursive(SimpleXMLElement $element, $level = 0) {   
	global $arrtcd;
	global $table;
	$indent     = str_repeat("\t", $level); // determine how much we'll indent
    
	$value      = trim((string) $element);  // get the value and trim any whitespace from the start and end
	$attributes = $element->attributes();   // get all attributes
	$children   = $element->children();     // get all children
	
	//echo "{$indent}Parsing '{$element->getName()}'...".PHP_EOL;
	if(count($children) == 0) // only show value if there is any and if there aren't any children
	{
			//echo "{$indent}Value: {$element}".PHP_EOL;
			switch ($element->getName()) {
				case "TestCaseName":
					$arrtcd[$element->getName()] = $value;
					
					//echo "{$indent}Value: {$element}".PHP_EOL;
					break;
				case "CaseDescription":
					$arrtcd[$element->getName()] = $value;
					
					//echo "{$indent}Value: {$element}".PHP_EOL;
					break;
				case "Column":
					$attr= trim((string) $element['name']);
					//$arrtcd[$attr] = $value;
					$table[$attr] = $value;
					
					//echo "{$indent}Attribute: {$element['name']}".PHP_EOL;
					//echo "{$indent}Value: {$element}".PHP_EOL;
					break;
		       default:
        			//echo "i is not equal to 0, 1 or 2";
					break;
			}
    }
   
	// only show attributes if there are any
	if(count($attributes) > 0)
	{
	//echo $indent.'Has '.count($attributes).' attribute(s):'.PHP_EOL;
		foreach($attributes as $attribute)
        {
		//echo "{$indent}- {$attribute->getName()}: {$attribute}".PHP_EOL;
		}
	}

	// only show children if there are any
	if(count($children)) {
		//echo $indent.'Has '.count($children).' child(ren):'.PHP_EOL;
		foreach($children as $child) {
			parse_recursive($child, $level+1); // recursion :)
		}
	}
	//	echo $indent.PHP_EOL; // just to make it "cleaner"
}






?>
