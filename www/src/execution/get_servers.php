<?php


$string = file_get_contents("../../tempdata/testservers.json");
$json_a = json_decode($string, true);
//$json_o = json_decode($string);


//echo $string;
//echo "<p>--------</p>";
//foreach($json_o->testserver as $t){
//	echo "Name=".$t->name."IP=".$t->ip;
//}	
//print_r($json_a);
//echo "<p>--------</p>";
echo json_encode($json_a);

?>
