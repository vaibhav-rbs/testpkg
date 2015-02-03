<html>
<head>

<?php


$username = $_REQUEST['username'];
$suitename = $_REQUEST['suitename'];



?>


</head>

<body>

	<div id="uploadPanel" class="easyui-panel" style="margin-left: 45px; margin-right: 45px;">  
		<div id="layoutUpload" class="easyui-layout" style="width: 100%; height: 100%;" fit="true">
			<form id="fmImage" action="upload_excel.php?username=<?php echo $username?>&suitename=<?php echo $suitename?>"
			method="post" enctype="multipart/form-data">
			<input type="file" name="csvfile" id="csvfile" /><br><br><br>
				<div align="center"><input type="submit" name="submit" value="Upload" /></div>
			</form>
		</div>
	</div>
</body>
</html>


