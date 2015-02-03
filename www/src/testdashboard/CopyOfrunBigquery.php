<?php
/****************************************************************************
 * If authorized, it will echo json string of the queries data
 * If not, it will return authorization Url
 ****************************************************************************/
session_start();
set_include_path("../../lib/" . PATH_SEPARATOR . get_include_path());
require_once 'Google/Client.php';
require_once 'Google/Service/Bigquery.php';

$queryString = $_POST['queryString'];

/******************************************************************************
 * Client ID information
 ******************************************************************************/
$client_id = '672466286514-j1glr8e2jmearequ0a79nnakjoscf6ol.apps.googleusercontent.com';
$client_secret = 'MHqe600XztQsO-yKNYWpmErZ';
$redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

/******************************************************************************
 * Make an API reuest on behalf of a user. We need to have a valid OAuth 2.0
 * token for the user, so we send them through a login flow.
 * Also, it is required to add scope to use bigquery service.
 ******************************************************************************/
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/bigquery");

/******************************************************************************
 * When we create the service here, we pass the client to it. The client then
 * queries the service for the required scopes, and uses that when generating
 * the authentication URL later.
 ******************************************************************************/
$service = new Google_Service_Bigquery($client);

/************************************************
  If we're logging out we just need to clear our
  local access token in this case
 ************************************************/
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}

/************************************************
  If we have a code back from the OAuth 2.0 flow,
  we need to exchange that with the authenticate()
  function. We store the resultant access token
  bundle in the session, and redirect to ourself.
 ************************************************/
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  $redirect = preg_replace('/src.*/', 'monitorMain.php', $redirect);
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

/************************************************
  If we have an access token, we can make
  requests, else we generate an authentication URL.
 ************************************************/
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
} else {
  $authUrl = $client->createAuthUrl();
}

/************************************************
  If we're signed in and have a request to shorten
  a URL, then we create a new URL object, set the
  unshortened URL, and call the 'insert' method on
  the 'url' resource. Note that we re-store the
  access_token bundle, just in case anything
  changed during the request - the main thing that
  might happen here is the access token itself is
  refreshed if the application has offline access.
 ************************************************/
if ($client->getAccessToken() && isset($_GET['url'])) {
  $url = new Google_Service_Urlshortener_Url();
  $url->longUrl = $_GET['url'];
  $short = $service->url->insert($url);
  $_SESSION['access_token'] = $client->getAccessToken();
}

if (
    $client_id == '<YOUR_CLIENT_ID>'
    || $client_secret == '<YOUR_CLIENT_SECRET>'
    || $redirect_uri == '<YOUR_REDIRECT_URI>') {
  echo missingClientSecretsWarning();
}

/*****************************************************
 * Do query
 *****************************************************/
if (isset($authUrl)) {
	// if not authorized, pass authorization Url
	echo json_encode(array('authUrl' => $authUrl));
} else {
	/**
	$projectId = "motorola.com:ds-prod";
	$datasetId = "moto_assist";
	$result = $service->tables->listTables($projectId, $datasetId, $optParams);
	print_r($result);
	foreach($result->tables as $table) {
		echo "id: ", $table[id], "</br>\n";
		echo "kind: ", $table[kind], "</br>\n";
		echo "projectId: ", $table[tableReference][projectId], "</br>\n";
		echo "datasetId: ", $table[tableReference][datasetId], "</br>\n";
		echo "tableId: ", $table[tableReference][tableId], "</br>\n";
	}*/	
	
	$projectId = "motorola.com:sandbox";
	
	$query = new Google_Service_Bigquery_QueryRequest();
	$query->setQuery($queryString);
	$response = $service->jobs->query($projectId, $query);
	
	//$jobId = "job_oOKKHMz54GQkpDg5rFm3pjVoK8o";
	//$result = $service->jobs->listJobs($projectId, $optParams);
	//$result = $service->jobs->query($projectId, $query, $optParams);
	//$result = $service->jobs->get($projectId, $optParams);
	//$result = $service->jobs->getQueryResults($projectId, $jobId, $optParams);
	
	$result = array();
	$fields = array();
	
	foreach ($response->schema->fields as $f) {
		array_push($fields, $f[name]);
	}
		
	foreach ($response->rows as $row) {
		$item = array();
		$fieldIndex = 0;
		foreach ($row[f] as $value) {
			$item[$fields[$fieldIndex]] = $value[v];
			$fieldIndex++;
		}
		array_push($result, $item);
	}
	
	echo json_encode($result);
}
?>