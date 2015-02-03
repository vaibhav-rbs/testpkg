<?php
require_once 'SOAP/Client.php';

$testJobFile = file_get_contents('/datafiles/testjob/' . $_GET['file']);
$arrTestJobFile = json_decode($testJobFile);

echo $arrTestJobFile->{'productHW'}; 
?>