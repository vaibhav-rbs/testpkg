<?php
include ('shared_data.php');

$project_name = "IKXREL1KK";

$cmd = 'curl -k -X POST --data "{\"where\":\"\'Project Key\' in (\'' . $project_name . '\')\"}" https://jsql.pcs.mot.com/rest/query/' . $user . '/' . $pass;

$curl_response = exec($cmd);

$out_a = array();

$json_a = json_decode($curl_response, true);

$num = count($json_a);

$out_str = null;
for ($i = 0; $i < $num ; $i++){
	if ($i != 0) $out_str = $out_str . "\n";
        $out_a[$i] = array();
        while(list($key, $value) = each($json_a[$i])){
                $key = str_replace(' ','_',$key);
                $key = str_replace('/s','s',$key);
                if ($value == null) $value = "";
                $out_a[$i][$key] = $value;
        }
        $out_str = $out_str . json_encode($out_a[$i]);
}
//print_r($out_a);

echo $out_str;



?>

