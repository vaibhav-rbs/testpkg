<?php


$user_name = $_REQUEST['user_name'];
//$user_name = "autotest";
$fname_array = null;
$dir = "/datafiles/runlistfiles/";
$fname_array[0]["id"] = "--Select runlist from list--";
$fname_array[0]["text"] = "--Select runlist from list--";
$fname_array[0]["selected"] = true;
$j = 1;

if (is_dir($dir)) {
	if ($dh = opendir($dir)) {
        	while (($file = readdir($dh)) !== false) {
                	if (!is_dir($file)){
                        	list($user, $filename) = split ("_", $file);
                                if ($user == $user_name){
					$pos = strpos($file, ".json");
					if ($pos === false){
						$fname_array[$j]["id"] = $file;
						$fname_array[$j]["text"] = $file;
                                        	$j++;
					}
                                }
                         }
                 }
         	 closedir($dh);
	}
}
//print_r($fname_array);
if ($fname_array != null){
	$fp = fopen('../../tempdata/select.json', 'w');
	fwrite($fp, json_encode($fname_array));
	fclose($fp);

	echo json_encode($fname_array);
} else {
	echo json_encode(array('msg'=>'Found no runlist files'));
}

?>
