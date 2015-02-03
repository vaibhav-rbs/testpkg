<!DOCTYPE HTML>
<html>
<body>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
<script type="text/javascript">
</script>

<form action="" method = "" id = "execute">
<?php
$con=mysqli_connect("127.0.0.1","root","root123","testdepot");
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
$result = mysqli_query($con,"SELECT * FROM tblDeviceRegistration");
echo "<table border='1'>
<tr>
<th>Device Serial Number</th>
<th>Device Time Stamp</th>
<th>Time</th>
<th>Date</th>
<th>NetworkType</th>
</tr>";
while($row = mysqli_fetch_array($result)) {
echo "<tr>";
$deviceID = $row['deviceSerialNumber'];
echo"<td><input type = 'radio' id = $deviceID value = $deviceID name = 'deviceID'>$deviceID</td>";
echo"<td>" . $row['DeviceTimeStamp'] . "</td>";
echo"<td>". $row['time'] . "</td>";
echo"<td>". $row['date'] . "</td>";
echo"<td>". $row['networkType'] . "</td>";
echo"</tr>";
echo "<br>";
}
?>

<script type="text/javascript">

$("input:radio[name=deviceID]").click(function() {
    var value = $(this).val();
    ajaxFunction(value);
});

function ajaxFunction(deviceID){
 var ajaxRequest;
 try{
   ajaxRequest = new XMLHttpRequest();
 }catch (e){
   try{
      ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
   }catch (e) {
      try{
         ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
      }catch (e){
         alert("Your browser broke!");
         return false;
      }
   }
 }
 ajaxRequest.onreadystatechange = function(){
   if(ajaxRequest.readyState == 4){
      var ajaxDisplay = document.getElementById('ajaxDiv');
      ajaxDisplay.innerHTML = ajaxRequest.responseText;
   }
 }

 var queryString = ""
 queryString =  queryString + "?deviceID=" + deviceID;
 ajaxRequest.open("GET", "ajax-example.php" + queryString, true);
 ajaxRequest.send(null); 
}
</script>
<div id='ajaxDiv'>Your result will display here</div>
</form>
</body>
</html>
