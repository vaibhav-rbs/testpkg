<?php                                         require_once 'SOAP/Client.php';
                                              include('../common/tc_functions.php');
                                             // include('../../clientMain.php');  
                                                $groupname=$_GET['group_name'];
						$testplan="Master Plan";
						$results = Get_Test_Plans($testplan,$groupname);
						$count_result=0;
						$xml= simplexml_load_string($results);
						$master_plan_list="";
		                                 
						if (count($xml->Table) > 0) {
							foreach ($xml->Table as $node) {
								$master_plan_list=$master_plan_list.'<li>';
								$master_plan_list= $master_plan_list.'<span class="jstree-closed" id="'.$node->testplanname.chr(10).'" value="0">'.$node->testplanname.chr(10).'</span>';
								$master_plan_list=$master_plan_list."</li>" ;
							}
						}
		                                        
						echo $master_plan_list;
						?>
