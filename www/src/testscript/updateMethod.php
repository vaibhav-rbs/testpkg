<?php
include 'testlibDB.php';

$id = $_REQUEST['id'];
$description = $_REQUEST['description'];
$example = $_REQUEST['example'];
$parameters = $_POST['parameters'];

echo updateMethod($id, $description, $example, $parameters);
?>