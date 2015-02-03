<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
<title>invader+ upload page</title>

<!-- stylesheet -->
<style type="text/css">
body.org {
        font-family:Arial, Helvetica, Sans-Serif;
        font-size:12px;
        margin:0px;
        height:100%;
}
</style>

<link type="text/css" href="themes/menu.css" rel="stylesheet" />

</head>
<body class="org">
	<!-- Start of Menu -->
	<ul id="menu" style="margin-left: 45px;">
		<li class="logo">
			<img style="float:left;" alt="" src="img/menu_left_invader.png"/>
		</li>
		<li>aPython Script Upload</li>
	</ul>
	<img style="float:left;" alt="" src="img/menu_right.png"/>
	<div style="float:none; clear:both;"></div>
    <!-- End of Menu -->
	<div id="uploadPanel" class="easyui-panel" style="margin-left: 45px; margin-right: 45px;">  
		<div id="layoutUpload" class="easyui-layout" style="width: 100%; height: 100%;" fit="true">
			<form id="fm2" action="upload_file.php?pname=<?php echo $package?>&mname=<?php echo $method?>&ofile=<?php echo $original_file?>&user_first_name=<?php echo $user_first_name?>" method="post" enctype="multipart/form-data">
        		<select id="sversion" name="sversion">
        			<option value="">Select Android Platform Version</option>
					<option value="JB">Jelly Bean</option>
					<option value="ICS">Ice Cream Sandwich</option>
				</select><br>
				<input type="file" name="file" id="file" /><br><br><br>
				<div align="center"><input type="submit" name="submit" value="Submit" /></div>
			</form>
		</div>
	</div>
</body>
</html>
