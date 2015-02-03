<?php

$user_name_php = $_POST['name'];
$user_pass_php = $_POST['password'];

$user_name_php = "vaibhav";
$user_first_name = "Vaibhav";
$user_last_name = "Chauhan";

$user_string = $user_name_php . "^" . $user_first_name . "^" .$user_last_name; 
header("Location: login.php?user_string=" . $user_string);
exit;

?>
