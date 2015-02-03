<?php
$filename = $_GET['filename'];
$testscriptJSON = "/datafiles/testscriptjson/" . $filename . ".json";
$testscriptJSON2 = "/datafiles/testscriptjson/" . $filename . "_affected.json";
$testscriptXML = "/datafiles/testscriptfiles/" . $filename . ".xml";

unlink($testscriptJSON);
unlink($testscriptJSON2);
unlink($testscriptXML);
?>