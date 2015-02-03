<?php
$userName = $_GET['user_first_name'];
$deviceName = $_GET['deviceID'];
$phoneNumber = $_GET['phoneNumber'];
$emailAddress = $_GET['emailAddress'];

exec("python /var/www/testPackageUploader.py $userName $deviceName $phoneNumber $emailAddress 2>&1", $output);
mysqli_close($con);
echo "<div id='clients' style='position:relative;white-space:nowrap;'>";
echo "</div>";
echo exec('whoami');
echo print_r($deviceName);
echo print_r($userName);
echo print_r($output);
?>
