<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
<title>Set Test Server</title>

<!-- stylesheet -->
<style type="text/css">
body.org {
	font-family:Arial, Helvetica, Sans-Serif; 
	font-size:12px;
}

#fm {
	margin: 0;
	padding: 10px 30px;
}

.ftitle {
	font-size: 14px;
	font-weight: bold;
	color: #666;
	padding: 5px 0;
	margin-bottom: 10px;
	border-bottom: 1px solid #ccc;
}

.fitem {
	margin-bottom: 5px;
}

.fitem label {
	display: inline-block;
	width: 80px;
}
</style>

<!-- jquery easy UI 1.2.4 plug ins -->
<link type="text/css" href="../../themes/default/easyuiInvaderPlus.css" rel="stylesheet" />
<link type="text/css" href="../../themes/icon.css" rel="stylesheet" />
<link type="text/css" href="../../themes/menu.css" rel="stylesheet" />
<link type="text/css" href="../../themes/main.css" rel="stylesheet" />
<script type="text/javascript" src="../../lib/jquery-1.6.min.js"></script>
<script type="text/javascript" src="../../lib/jquery.easyui.min.js"></script>
<script type="text/javascript" src="profile.js"></script>
<!-- script includes all the common JS functions -->

<?php

    session_start();
    $user_name = $_SESSION['username'];


?>


<script type="text/javascript">
    var username;
        username="<? echo $user_name;?>";

</script>

</head>
<body class="org">

   <table id="userProp" class="easyui-propertygrid" style="width:1000px; height:250px;" 
	url="../../tempdata/profile_data/<?php echo $user_name?>_prop.json"
	title = "Set Up Default Test Server"
	toolbar = "#userProp-toolbar">

   </table>
   <div id="userProp-toolbar">  
    <a href="#" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="save_userProp()">Save</a>  
    <a href="#" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancel_userProp()">Cancel</a>
   </div>  


</body>
</html>
