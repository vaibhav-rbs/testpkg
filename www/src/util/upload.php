<?php
if ($_FILES["logs"]["error"] > 0)
  {
  echo "Error: " . $_FILES["logs"]["error"] . "<br>";
  }
else
  {
  echo "Upload: " . $_FILES["logs"]["name"] . "<br>";
  echo "Type: " . $_FILES["logs"]["type"] . "<br>";
  echo "Size: " . ($_FILES["logs"]["size"] / 1024) . " kB<br>";
  move_uploaded_file($_FILES["logs"]["tmp_name"],
      "/datafiles/upload_to_gcs/" . $_FILES["logs"]["name"]);
  echo "Stored in: " . "/datafiles/upload_to_gcs/" . $_FILES["logs"]["name"];
  }
?>
