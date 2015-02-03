<?php
require_once 'SOAP/Client.php';

$testcasename = 'MDB APENG.ARIA NA.Chromium:001-001';

$executionServiceWsdlUrl = 'http://testcentral.mot.com/webservices/interface_ArchitectService.asmx?WSDL';
$executionServiceWsdl     = new SOAP_WSDL($executionServiceWsdlUrl);
$executionServiceClient   = $executionServiceWsdl->getProxy();
$executionServiceClient->setOpt('timeout', 500);
$executionHistory = $executionServiceClient->Interface_GetTestCaseDetailsByTestCase($testcasename);
echo $executionHistory;
?>