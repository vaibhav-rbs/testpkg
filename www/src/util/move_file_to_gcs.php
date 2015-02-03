<?php

// run this way: nohup php move_file_to_gcs.php > /datafiles/move_file_to_gcs.out 2>&1&
//

while(1){
if ($DIR = opendir('/datafiles/upload_to_gcs')){
	while (false !== ($filename = readdir($DIR)) ){
		if(strpos($filename,'.zip') !== false){
			list($file0,$zip_remove) = split(".zip", $filename);
			$complete_file = "/datafiles/upload_to_gcs/" . $file0 . ".complete";
			if(file_exists($complete_file)) {	
				echo date("Y-m-d H:i:s") . " ";
				echo $filename . "\n";
				echo date("Y-m-d H:i:s") . " ";
				for ($i=0 ; $i < 30 ; $i++){
					$file_path = "/datafiles/upload_to_gcs/" . $filename;
					$target_path = "/tmp/gcs/.";
					$cmd0 = "cp \"" . $file_path . "\" \"" . $target_path . "\"";
					exec($cmd0);
					$cmd0 = "cd /tmp/gcs; unzip \"" . $filename . "\"";
					exec($cmd0);

					$target_path2 = "/tmp/gcs/" . $file0;
					$target_path3 = "/tmp/gcs/" . $filename;

					archive_report($target_path2,$file0);

					$cmd = "/home/testautoteam/gsutil_source/gsutil/gsutil -m cp -R \"" . $target_path2 . "\" gs://testdepot2";
					exec($cmd);

					$cmd2 = "/home/testautoteam/gsutil_source/gsutil/gsutil ls gs://testdepot2/\"" . $file0 . "\"";
					$ret2 = exec($cmd2);
					$match = "No such object";
					if (strpos($ret2,$match) !== false) {
					}else{
						$cmd4 = "/home/testautoteam/gsutil_source/gsutil/gsutil -m acl ch -R -g mmiall2@motorola.com:R gs://testdepot2/\"" . $file0 . "\"";
						exec($cmd4);
   						$i = 30;
					}
					//echo "i=" . $i . "\n";
				}
				$cmd3 = "sudo rm -r \"" . $target_path2 . "\"";
				exec($cmd3); 
				$cmd3 = "sudo rm \"" . $target_path3 . "\"";
				exec($cmd3); 
				$cmd6 = "sudo rm \"" . $complete_file . "\"";
				exec($cmd6);
				$cmd6 = "sudo rm \"" . $file_path . "\"";
				exec($cmd6);
			}
		}
	}
	closedir($DIR);

}

sleep(60);

}


function archive_report($dir,$file0){

	$cmd = "cd \"" . $dir . "\";find . -name merged_report.xml";
        $a_array = array();
        $ret_str = exec($cmd,$a_array);
        $a_count = count($a_array);
        for($i = 0 ; $i < $a_count ; $i++){
		list($str,$junk) = split("/merged_report.xml",$a_array[$i]);
		list($c,$a,$b) = split("/", $str);
		$org = $dir . "/" . $a . "/" . $b . "/merged_report.xml";
		$dest = "/datafiles/testresult/" . $file0 . "_" . $a . "_" . $b . ".xml";
		//print $org . "\n";
		//print $dest . "\n";
		$cmd2 = "cp \"" . $org . "\" \"" . $dest . "\"";
		exec($cmd2);
        }

}


?>
