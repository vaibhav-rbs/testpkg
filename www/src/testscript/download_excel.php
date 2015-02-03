<?php
require_once 'SOAP/Client.php'; 
require_once 'BasicExcel/Reader.php'; 

BasicExcel\Reader::registerAutoloader();

$username = $_REQUEST['username'];
$nodeID = $_REQUEST['suite'];


//$username = 'crmg76';
//$nodeID = 'suite^newsuite.test:072';
//$nodeID = 'suite^MDB APENG.ARIA NA.Feature Test:001';
$suiteName = substr($nodeID, strpos($nodeID, "^") + 1);
$info_array = getTestCasesJson($suiteName);

//print_r($info_array);

$info_sub = array();
$columns_sub = array();

$info_sub = $info_array['info'];
$columns_sub = $info_array['columns'];

//print($info_sub[0]['columns'][0]['column']['name']);
//print($info_sub[0]['columns'][0]['column'][0]);

$data = array();

$col_list_total = count($columns_sub);
array_push($data, $columns_sub);
$info_count = count($info_sub);
for($i=0 ; $i<$info_count ; $i++){
	$col_count = count($info_sub[$i]['columns']);
	$col_data = array($info_sub[$i]['name'],$info_sub[$i]['desc']);
	for($j=0 ; $j<$col_count ; $j++){
		$col_name = (string)$info_sub[$i]['columns'][$j]['column']['name'];
		$col_text = (string)$info_sub[$i]['columns'][$j]['column'][0];
		
		for($k=2 ; $k<$col_list_total ; $k++){
			if(!strcmp($col_name,$columns_sub[$k])){
				$col_data[$k] = $col_text;

			}

		}
		for($k=2 ; $k<$col_list_total ; $k++){
			if(is_null($col_data[$k])){
				$col_data[$k] = "";
			}
		}

	}
	array_push($data, $col_data);

}

$new_data = new_format_data($data);


//$fp = fopen('/tmp/suite.json', 'w');
//fwrite($fp, json_encode($new_data));
//fclose($fp);




try {
    $csvwriter = new BasicExcel\Writer\Csv(); //or \Xsl || \Xslx
    $csvwriter->fromArray($new_data);
/*
    $file = $username . "suite" . ".csv";
    $file2 = '/tmp/' . $file;
    $csvwriter->writeFile($file);
    $cmd = "mv $file $file2";
    exec($cmd);
*/
    //OR
    $csvwriter->download($username . 'suite.csv');
} catch (Exception $e) {
    $message = $e->getMessage();
}
//downloadFile($file2,$file,900,false);

function getTestCasesJson($suite){
	$columns_list = array("Test Case Name","Description");
	$info =array();
	$xml = simplexml_load_string(Get_Test_Case_By_Suite($suite));

	//print_r($xml);	
	if(count($xml->Table) > 0){
		$result = array();
		
		foreach ($xml->Table as $tableList){
			$case = array();
			$case_name = trim($tableList->TestCaseName);
			$case['name'] = $case_name;
			$case['desc'] = trim($tableList->CaseDescription);


			$xml2 = simplexml_load_string(Get_Test_CaseDetails_By_TestCase($case_name));

                	//echo count($xml2->Table->TestCaseTPSData->XMLDATA->COLUMNS->Column);
                	if (count($xml2->Table->TestCaseTPSData->XMLDATA->COLUMNS->Column) > 0) {
                       		$columns = array();

                       		foreach ($xml2->Table->TestCaseTPSData->XMLDATA->COLUMNS->Column as $col) {

                               		//echo $col . "<br>";
					//print_r($xml2->Table);
					$c_name = (string)$col['name'];
					if (strcmp($c_name,"Script_Path")){ 
                               			$column['column'] = $col;
                               			array_push($columns, $column);
						if(!in_array($c_name, $columns_list)){
                               				array_push($columns_list, $c_name);
						}
					}
                       		}
			}
                        $case['columns'] = $columns;
			array_push($result, $case);
		}
		
	}
	$info['info'] = $result;
	$info['columns'] = $columns_list;
	return $info;
}

function downloadFile($fileLocation,$fileName,$maxSpeed = 100,$doStream = false){
		if (connection_status()!=0) return(false); 
		$extension = strtolower(end(explode('.',$fileName))); 

		/* List of File Types */ 
                $fileTypes['swf'] = 'application/x-shockwave-flash'; 
                $fileTypes['pdf'] = 'application/pdf'; 
                $fileTypes['exe'] = 'application/octet-stream'; 
                $fileTypes['csv'] = 'application/csv'; 
                $fileTypes['zip'] = 'application/zip'; 
                $fileTypes['doc'] = 'application/msword'; 
                $fileTypes['xls'] = 'application/vnd.ms-excel'; 
                $fileTypes['ppt'] = 'application/vnd.ms-powerpoint'; 
                $fileTypes['gif'] = 'image/gif'; 
                $fileTypes['png'] = 'image/png'; 
                $fileTypes['jpeg'] = 'image/jpg'; 
                $fileTypes['jpg'] = 'image/jpg'; 
                $fileTypes['rar'] = 'application/rar';     

                $fileTypes['ra'] = 'audio/x-pn-realaudio'; 
                $fileTypes['ram'] = 'audio/x-pn-realaudio'; 
                $fileTypes['ogg'] = 'audio/x-pn-realaudio'; 

                $fileTypes['wav'] = 'video/x-msvideo'; 
                $fileTypes['wmv'] = 'video/x-msvideo'; 
                $fileTypes['avi'] = 'video/x-msvideo'; 
                $fileTypes['asf'] = 'video/x-msvideo'; 
                $fileTypes['divx'] = 'video/x-msvideo'; 

                $fileTypes['mp3'] = 'audio/mpeg'; 
                $fileTypes['mp4'] = 'audio/mpeg'; 
                $fileTypes['mpeg'] = 'video/mpeg'; 
                $fileTypes['mpg'] = 'video/mpeg'; 
                $fileTypes['mpe'] = 'video/mpeg'; 
                $fileTypes['mov'] = 'video/quicktime'; 
                $fileTypes['swf'] = 'video/quicktime'; 
                $fileTypes['3gp'] = 'video/quicktime'; 
                $fileTypes['m4a'] = 'video/quicktime'; 
                $fileTypes['aac'] = 'video/quicktime'; 
                $fileTypes['m3u'] = 'video/quicktime'; 

                $contentType = $fileTypes[$extension]; 


                header("Cache-Control: public"); 
                header("Content-Transfer-Encoding: binary\n"); 
                header('Content-Type: $contentType'); 

                $contentDisposition = 'attachment'; 

                if($doStream == true){ 
                    /* extensions to stream */ 
                    $array_listen = array('mp3','m3u','m4a','mid','ogg','ra','ram','wm', 
                    'wav','wma','aac','3gp','avi','mov','mp4','mpeg','mpg','swf','wmv','divx','asf'); 
                    if(in_array($extension,$array_listen)){  
                        $contentDisposition = 'inline'; 
                    } 
                } 

                if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) { 
                    $fileName= preg_replace('/\./', '%2e', $fileName, substr_count($fileName,'.') - 1);
                    header("Content-Disposition: $contentDisposition;filename=\"$fileName\"");
                } else { 
                    header("Content-Disposition: $contentDisposition;filename=\"$fileName\"");
                } 

                header("Accept-Ranges: bytes");    
                $range = 0; 
                $size = filesize($fileLocation); 

                if(isset($_SERVER['HTTP_RANGE'])) { 
                    list($a, $range)=explode("=",$_SERVER['HTTP_RANGE']); 
                    str_replace($range, "-", $range); 
                    $size2=$size-1; 
                    $new_length=$size-$range; 
                    header("HTTP/1.1 206 Partial Content"); 
                    header("Content-Length: $new_length"); 
                    header("Content-Range: bytes $range$size2/$size"); 
                } else { 
                    $size2=$size-1; 
                    header("Content-Range: bytes 0-$size2/$size"); 
                    header("Content-Length: ".$size); 
                } 
                if ($size == 0 ) { die('Zero byte file! Aborting download');} 
                set_magic_quotes_runtime(0);  
                $fp=fopen("$fileLocation","rb"); 

                fseek($fp,$range); 

                while(!feof($fp) and (connection_status()==0)) 
                { 
                    set_time_limit(0); 
                    print(fread($fp,1024*$maxSpeed)); 
                    flush(); 
                    ob_flush(); 
                    sleep(1); 
                } 
                fclose($fp); 

                return((connection_status()==0) and !connection_aborted()); 
}  





function Get_Test_Case_By_Suite($suite) {
        //echo $suite;echo 'hardcode';
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
        $executionServiceClient   = $executionServiceWsdl->getProxy();
        $executionServiceClient->setOpt('timeout', 500);
        $executionHistory = $executionServiceClient->Interface_GetCaseGeneralInfo($suite);
        return $executionHistory;
}


function Get_Test_CaseDetails_By_TestCase($testcasename)
{
        //echo $suite;echo 'hardcode';
        $executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
        $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
        $executionServiceClient   = $executionServiceWsdl->getProxy();
        $executionServiceClient->setOpt('timeout', 500);
        $executionHistory = $executionServiceClient->Interface_GetTestCaseDetailsByTestCase($testcasename);
        return $executionHistory;
}


function new_format_data($data){

	$new_data = array();
	$new_data[0] = $data[0];
	$count = count($data);

	for($i = 1; $i < $count ; $i++){
		$tmp_array = array();
		$tmp_array[0] = array();
		$tmp_array[0] = explode('\\r\\n', $data[$i][2]);

		$first_column = $tmp_array[0];
		$row_count = count($first_column);
	
		$data_column_count = count($data[$i]) - 2;

		for($n = 1 ; $n < $data_column_count ; $n++){
			$tmp_array[$n] = array();
			$m = $n + 2;
			$tmp_array[$n] = explode('\\r\\n', $data[$i][$m]); 

		}

		$target = array();
		array_push($target, $data[$i][0]);
		array_push($target, $data[$i][1]);
		for($nn = 0 ; $nn < $data_column_count ; $nn++){
			array_push($target, $tmp_array[$nn][0]);
		}

		array_push($new_data, $target);	

		for ($j=1 ; $j < $row_count; $j++){
			$target1 = array();
			array_push($target1,"");
			array_push($target1,"");
			for($n2 = 0 ; $n2 < $data_column_count ; $n2++){
				array_push($target1, $tmp_array[$n2][$j]);
			}
			if(!is_null($target1)){
				$flag = 0;
				for ($k1 = 0 ; $k1 < $data_column_count+2 ; $k1++){
					if(!is_null($target1[$k1]) && $target1[$k1] != ""){
						$flag = 1;
						break;
					}
				}
				if($flag == 1) array_push($new_data, $target1);
			}	
		}
	}



	return $new_data;

}

?>
