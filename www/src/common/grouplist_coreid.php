<?php
                                              require_once 'SOAP/Client.php';
                                              include('./tc_functions.php');
                                              include('../../clientMain.php'); 
						$coreid=$user_name_php;
                                                $groupnames = GetAllGroupsByCoreid($coreid);
                                                $xml= simplexml_load_string($groupnames);
						$groupname_list="";

                                                if (count($xml->Table) > 0) {
						       foreach ($xml->Table as $node) {
                                                                 $groupname_list=$groupname_list.'<option value="'.$node->groupName.'">'.$node->groupName.'</option>';
							}
						}
		                                        
					        echo $groupname_list;    
                                      
?>
