<?php

//require_once 'google-api-php-client/src/apiClient.php';
//require_once 'google-api-php-client/src/contrib/apiBigqueryService.php';

set_include_path("../../lib/" . PATH_SEPARATOR . get_include_path());
require_once 'Google/Client.php';
require_once 'Google/Service/Bigquery.php';

session_start();

$client = new Google_Client();
// Visit https://developers.google.com/console to generate your
// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.

$client->setClientId('672466286514-j1glr8e2jmearequ0a79nnakjoscf6ol.apps.googleusercontent.com');
$client->setClientSecret('MHqe600XztQsO-yKNYWpmErZ');
$client->setRedirectUri("http://localhost/webClient_1.0.0/src/testdashboard/run_big_query.php");

// Your project id
$project_id = 'motorola.com:sandbox';

// Instantiate a new BigQuery Client 
$bigqueryService = new Google_Service_Bigquery($client);

if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}

if (isset($_SESSION['access_token'])) {
  $client->setAccessToken($_SESSION['access_token']);
} else {
  $_SESSION['access_token'] = $client->getAccessToken();
}

if (isset($_GET['code'])) {
  $client->setAccessToken($client->authenticate($_GET['code']));
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}
?>
<!doctype html>
<html>
<head>
  <title>BigQuery API Sample</title>
</head>
<body>
<div id='container'>
  <div id='top'><h1>BigQuery API Sample</h1></div>
  <div id='main'>
<?php
  $query = new Google_Service_Bigquery_QueryRequest();
  $query->setQuery('SELECT TOP( title, 10) as title, COUNT(*) as revision_count FROM [publicdata:samples.wikipedia] WHERE wp_namespace = 0;');

  $jobs = $bigqueryService->jobs;
  $response = $jobs->query($project_id, $query);

  // Do something with the BigQuery API $response data
  print_r($response);

?>
</div>
</div>
</body>
</html>