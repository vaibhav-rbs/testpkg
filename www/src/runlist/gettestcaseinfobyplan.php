<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP

$username=$_GET['user_name'];
//$username="prx384";
$username=$username."_";
$runlist_file=$_GET['test_plan'];
$runlist_file=$runlist_file.".xml";
$runlist_file="/datafiles/runlistfiles/".$username.$runlist_file;

$fh=fopen($runlist_file,'r') ;

if($fh)
{
$thedata=fread($fh,filesize($runlist_file));
$xml=simplexml_load_string($thedata);
$xml->registerXPathNamespace('s','com.motorola.wireless.qa.invaderPlus');
$result= $xml->xpath('//s:call/@file');

fclose($fh);
}


//creating a hashmap for lookup
foreach($result as $testcase_runlist)
         {   
             $runlist_testcases['"'.$testcase_runlist.'"']=$testcase_runlist;
             
         } 


$testplan3=$_GET['test_plan'];
$results3 = Get_Test_Case($testplan3);

$array_count=0;

$cycle_plan_info='{"rows":[';
$cycle_plan_info_1='{';              //echo back to client to create a datastructure at the client side
                


$xml3= simplexml_load_string($results3);
 
$flag_testcase=0;             // indicates testcase entry exists in runlist
$flag_testcase_1=0;           // indicates testscript exits
$count_value = count($xml3);
if (count($xml3->Table) > 0) {
    //$cycle_plan_info=$cycle_plan_info.'<tbody>';
    foreach ($xml3->Table as $node3) {
        // This prints out each of the models
        $array_count=$array_count+1;
        $cycle_plan_info=$cycle_plan_info.'{';
       
        

                        $testscript_file=$node3->testCaseName;//check for automated/manual
			 $testscript_file=$testscript_file.".xml";
			  $testscript_file="/datafiles/testscriptfiles/".$testscript_file;
		       //$runlist_file="/home/bluremployee/runlist_dir/".$_GET['test_plan'];
			 $fh1=fopen($testscript_file,'r') ;
			  if($fh1)
			  {
			  $flag_testcase_1=1;
			  }

        if($fh)
       {
			

          if($runlist_testcases['"'.$node3->testCaseName.'.xml'.'"']==$node3->testCaseName.'.xml' && $flag_testcase_1==1)
          {
            $flag_testcase=1;
          }
          

        }

        $cycle_plan_info_1=$cycle_plan_info_1.'"'.$node3->testCaseName.'":';        


        if( $flag_testcase==1)
        {
            $cycle_plan_info=$cycle_plan_info.'"ck":"true",';
            $cycle_plan_info=$cycle_plan_info.'"test_runlist":"1",';

            $cycle_plan_info_1=$cycle_plan_info_1.'{"test_runlist":"1",';

        }

        else
         {
           $cycle_plan_info=$cycle_plan_info.'"ck":"true",';
            $cycle_plan_info=$cycle_plan_info.'"test_runlist":"0",';
           //$cycle_plan_info=$cycle_plan_info.'"ck":"true",';
            $cycle_plan_info_1=$cycle_plan_info_1.'{"test_runlist":"0",';  // removed braces here 
          
         }

         $cycle_plan_info=$cycle_plan_info.'"testid":"'.$node3->testCaseName.'",';

         $testname_info=filter_data($node3->caseDescription);
         $cycle_plan_info=$cycle_plan_info.'"testname":"'.$testname_info.'",';
         
          $cycle_plan_info_1=$cycle_plan_info_1.'"testname":"'.$testname_info.'",';


         if( $flag_testcase==1)
         {
                if($flag_testcase_1==1)
                {
                $cycle_plan_info=$cycle_plan_info.'"exectype":"Automated",';

                $cycle_plan_info_1=$cycle_plan_info_1.'"exectype":"Automated",';
                }
                else
                {
                 $cycle_plan_info=$cycle_plan_info.'"exectype":"Manual",';
 
                 $cycle_plan_info_1=$cycle_plan_info_1.'"exectype":"Manual",';
                 
                }
            
                 
                // $cycle_plan_info=$cycle_plan_info.'"ck":"true",';
        
            //commented the code for now
                 
                 $attributes=$xml->xpath('//s:call[@file="'.$node3->testCaseName.'.xml'.'"]');
		 if($attributes[0][@count])
		 {
		 $cycle_plan_info=$cycle_plan_info.'"iteration":"'.$attributes[0][@count].'",'; //'<td class="editable">'.$attributes[0][@count].'</td>';
                  
                 $cycle_plan_info_1=$cycle_plan_info_1.'"iteration":"'.$attributes[0][@count].'",';   //adding config to the data structure
		 }
		 else
		 {
		  $cycle_plan_info=$cycle_plan_info.'"iteration":"1",'; //'<td class="editable">Default Count</td>';

                  $cycle_plan_info_1=$cycle_plan_info_1.'"iteration":"1",';   //adding config to the data structure
		 }
		 if($attributes[0][@delay])
		 {
                     $delay_time=replace_hms($attributes[0][@delay]);
		     $cycle_plan_info=$cycle_plan_info.'"delay":"'.$delay_time.'",';  //'<td class="editable">'.$attributes[0][@duration].'</td>';
                     $cycle_plan_info_1=$cycle_plan_info_1.'"delay":"'.$delay_time.'",';   //adding config to the data structure
		 }
		 else
		 {
		    $cycle_plan_info=$cycle_plan_info.'"delay":"00:00:00",';      //'<td class="editable">Default Duration</td>';
                    $cycle_plan_info_1=$cycle_plan_info_1.'"delay":"00:00:00",';   //adding config to the data structure
		 }
		 if($attributes[0][@duration])
		 {
                      $duration_time=replace_hms($attributes[0][@duration]);
		      $cycle_plan_info=$cycle_plan_info.'"duration":"'.$duration_time.'"';   //'<td class="editable">'.$attributes[0][@delay].'</td>';
                      $cycle_plan_info_1=$cycle_plan_info_1.'"duration":"'.$duration_time.'"},';   //adding config to the data structure
		 }
		 else
		 {
		   $cycle_plan_info=$cycle_plan_info.'"duration":"00:00:00"';   //'<td class="editable">Default Delay</td>';  
                        
                   $cycle_plan_info_1=$cycle_plan_info_1.'"duration":"00:00:00"},';   //adding config to the data structure
		 } 
         }
         else
         {      if($flag_testcase_1==1)
                {
                $cycle_plan_info=$cycle_plan_info.'"exectype":"Automated",';

                 $cycle_plan_info_1=$cycle_plan_info_1.'"exectype":"Automated",';
                }
                else
                {
                 $cycle_plan_info=$cycle_plan_info.'"exectype":"Manual",';

                 $cycle_plan_info_1=$cycle_plan_info_1.'"exectype":"Manual",';
                }
                         $cycle_plan_info=$cycle_plan_info.'"iteration":"1",';
			 $cycle_plan_info=$cycle_plan_info.'"delay":"00:00:00",';
			 $cycle_plan_info=$cycle_plan_info.'"duration":"00:00:00"';   

                         $cycle_plan_info_1=$cycle_plan_info_1.'"iteration":"1",';   //adding config to the data structure
                         $cycle_plan_info_1=$cycle_plan_info_1.'"delay":"00:00:00",';   //adding config to the data structure
                         $cycle_plan_info_1=$cycle_plan_info_1.'"duration":"00:00:00"},';   //adding config to the data structure
          }
         $flag_testcase=0;
         $flag_testcase_1=0;
         
         $cycle_plan_info=$cycle_plan_info.'},';
         
         
         
       
    }
  
}
$cycle_plan_info=substr($cycle_plan_info,0, strlen($cycle_plan_info)-1);
$cycle_plan_info=$cycle_plan_info.']}';

$cycle_plan_info_1=substr($cycle_plan_info_1,0, strlen($cycle_plan_info_1)-1);
$cycle_plan_info_1=$cycle_plan_info_1.'}';


echo $cycle_plan_info_1;
$datagrid_file="../../tempdata/datagrid_data.json";
$fh2=fopen($datagrid_file,'w') or die("cant open file");
fwrite($fh2,$cycle_plan_info);
fclose($fh2);




function Get_Test_Case($plan)
{   
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	 $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl); 
	 $executionServiceClient   = $executionServiceWsdl->getProxy(); 
        $executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestCaseInfoByPlan($plan);
	
	return $executionHistory; 
}

function filter_data($text)
{   $text = $text.trim('');
    $text=preg_replace("/[^a-z \d : . ( ) \/\/ { }  \/n \/t \/s]*/i", "", $text);
    $newchar = $text;
    return $newchar;
    
}

function replace_hms($time)
{
        $time1=str_replace("h",":",$time);
        $time2=str_replace("m",":",$time1);
        $time3=str_replace("s","",$time2);
        return $time3;

}


?>

