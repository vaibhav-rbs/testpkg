<?php
	$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
	
       $rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
       
       $sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'itemid';
       $order = isset($_POST['order']) ? strval($_POST['order']) : 'asc';
       
	$offset = ($page-1)*$rows;   // getting the pagination information ,i.e. page number and number of rows in the datagrid.
	
	$result = array();


        $runlist_file="../../tempdata/datagrid_data.json";      //reading json information about test cases from the .json file .

	$fh=fopen($runlist_file,'r') ;

	if($fh)
	{
	$thedata=fread($fh,filesize($runlist_file));
	}

        $json_a=json_decode($thedata,true);
        
        $testid_array=array();

       foreach($json_a["rows"] as $testid_arrays)
       {
           $testid_array[]=$testid_arrays[$sort];
       }

       if($order=="asc")
       {
       array_multisort($testid_array, SORT_ASC, $json_a["rows"]);
        }
       else
        {
          array_multisort($testid_array, SORT_DESC, $json_a["rows"]);
        }
        

        // creating json string for the subset of test cases to be displayed on the datagrid .
        $cycle_plan_info='{"total":'.count($json_a["rows"]).',"rows":[';

         if(count($json_a["rows"])<($offset+$rows))
          $var_count=count($json_a["rows"]);
         else
           $var_count=$offset+$rows;

	      for($i=($offset);$i<=($var_count-1);$i++)
	      {

		   $cycle_plan_info=$cycle_plan_info.'{';
		   $cycle_plan_info=$cycle_plan_info.'"test_runlist":"'.$json_a["rows"][$i]["test_runlist"].'",';
		   
		   $cycle_plan_info=$cycle_plan_info.'"testid":"'.$json_a["rows"][$i]["testid"].'",';
                   $cycle_plan_info=$cycle_plan_info.'"testname":"'.$json_a["rows"][$i]["testname"].'",';
		   $cycle_plan_info=$cycle_plan_info.'"exectype":"'.$json_a["rows"][$i]["exectype"].'",';
                    $cycle_plan_info=$cycle_plan_info.'"iteration":"'.$json_a["rows"][$i]["iteration"].'",';
                   $cycle_plan_info=$cycle_plan_info.'"delay":"'.$json_a["rows"][$i]["delay"].'",';
                   $cycle_plan_info=$cycle_plan_info.'"duration":"'.$json_a["rows"][$i]["duration"].'"';
		            $cycle_plan_info=$cycle_plan_info.'},';
	           //echo $json_a["rows"][0]["testid"];
	
	        }


     $cycle_plan_info=substr($cycle_plan_info,0, strlen($cycle_plan_info)-1);
     $cycle_plan_info=$cycle_plan_info.']}';


echo $cycle_plan_info;
  


	
?>
