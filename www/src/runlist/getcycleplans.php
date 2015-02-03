 
<?php
require_once 'SOAP/Client.php'; //http://pear.php.net/package/SOAP

$masterplan=$_GET['master_plan'];
$groupname=$_GET['groupname_cycle'];
                          $masterplanname=$masterplan.'';
                       
                          $testplan1="Cycle Plan";
                          $results2 = Get_Test_Plans($testplan1,$groupname);
                         // echo $results;
                          $xml2= simplexml_load_string($results2);
                          $cycle_plan_list=$cycle_plan_list."[";
				if (count($xml2->Table) > 0) {
				    foreach ($xml2->Table as $node2) {
					if($masterplanname==$node2->parentdetail.'')
                                        {
                                        
					//$cycle_plan_list= $cycle_plan_list.'<li class="cycle" id="'.$node2->testplanname.'" value="1"><a href="#">'.$node2->testplanname.chr(10).'</a></li>'; SHOULD BE DELETED LATER
                                        // $cycleplan_name=str_replace(" ","_",$node2->testplanname); // if the spaces in cycleplan names needs to be replaced by '_' , use this line
                                           $cycleplan_name=$node2->testplanname;
                                         $cycle_plan_list=$cycle_plan_list.'{id:"1",text:"'.$cycleplan_name.'"},' ;        
                                                
                                        
                                         }
		

 
				       
				    }
				}
                         
			  $cycle_plan_list=substr($cycle_plan_list,0, strlen($cycle_plan_list)-1);	
                          $cycle_plan_list=$cycle_plan_list."]";




         $count_result++;
 
 //$cycle_plan_json= json_encode($cycle_plan_list);

$cycle_plan_list=$cycle_plan_list.'';

echo $cycle_plan_list;

function Get_Test_Plans($plan,$groupname)
{   
	$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_planningService.asmx?WSDL';
	 $executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl); 
	 $executionServiceClient   = $executionServiceWsdl->getProxy(); 
        $executionServiceClient->setOpt('timeout', 200);
	$executionHistory = $executionServiceClient->Interface_GetTestPlans($plan,Testing,"and tp.groupId in(select groupId from groups where groupName = '".$groupname."')");
	
	return $executionHistory; 
}

?>
