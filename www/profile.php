<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
<title>invader+ profile page</title>

<!-- stylesheet -->
<style type="text/css">
#fm {
        margin: 0;
        padding: 10px 30px;
}
#jiralink {
        text-align: right;
        padding: 0px 20px;
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
body.org {
        font-family:Arial, Helvetica, Sans-Serif;
        font-size:12px;
        margin:0px;
        height:100%;
        margin-left: 5px;
}

</style>




<!-- jquery easy UI 1.2.4 plug ins -->
<link type="text/css" href="themes/default/easyuiInvaderPlus.css" rel="stylesheet" />
<link type="text/css" href="themes/default/treeModule.css" rel="stylesheet" />
<link type="text/css" href="themes/icon.css" rel="stylesheet" />
<link type="text/css" href="themes/menu.css" rel="stylesheet" />
<link type="text/css" href="themes/main.css" rel="stylesheet" />
<link type="text/css" href="themes/dragdrop.css" rel="stylesheet" />
<link type="text/css" href="src/testscript/testscript.css" rel="stylesheet" />
<script type="text/javascript" src="lib/jquery-1.6.min.js"></script>
<script type="text/javascript" src="lib/jquery.easyui.min.js"></script>
<script type="text/javascript" src="src/profile/profile.js"></script>
<!-- script includes all the common JS functions -->



<?php


$user_name_php = $_REQUEST['user_name'];

session_start();
$user_first_name = $_SESSION['firstname'];





?>

<script type="text/javascript">
    var username;
    username="<? echo $user_name_php;?>";

</script>
</head>
<body class="org">

        <!-- Start of Menu -->
        <div>
                <ul id="menu">
                        <li class="logo">
                                <img style="float:left;" alt="" src="img/menu_left_invader.png"/>
                                <ul id="main">
                                        <li>Version 3.2.0</li>
                                        <li class="last">
                                <img class="corner_left" alt="" src="img/corner_blue_left.png"/>
                            <img class="middle" alt="" src="img/dot_blue.png"/>
                            <img class="corner_right" alt="" src="img/corner_blue_right.png"/>
                                        </li>
                                </ul>
                        </li>
			<li>Welcome <?php echo $user_first_name?>!</li>
                        <li>
				<a href="#" onclick="window.close();">Exit</a>
                        </li>

                        <li style="width:130px;">
                                <a href="#" style="color:#B0D730;">Help</a>
                                <ul id="help">
                                        <li>
                                                <img class="corner_inset_left" alt="" src="img/corner_inset_left.png"/>
                                                <a target="_blank" href="https://sites.google.com/a/motorola.com/invaderplus/">User Manual</a>
                                                <img class="corner_inset_right" alt="" src="img/corner_inset_right.png"/>
                                        </li>
                                        <li>
                                                <a target="_blank" href="tempdata/UserManual.pdf">Test Library Doc</a>
                                        </li>
                                        <li>
                                                <a href="#" onclick="javascript:openJira()">Report a problem</a>
                                        </li>
                                        <li class="last">
                                <img class="corner_left" alt="" src="img/corner_left.png"/>
                                <img class="middle" alt="" src="img/dot.gif"/>
                                <img class="corner_right" alt="" src="img/corner_right.png"/>
                                </li>
                                </ul>
                        </li>

                </ul>
                <img style="float:left;" alt="" src="img/menu_right.png"/>
        </div>
        <div style="float:none; clear:both;"></div>
    <!-- End of Menu -->

	<div id="adminPanel" class="easyui-panel">  
                        <div id="layoutAdmin" class="easyui-layout"
                                style="width: 100%; height: 100%;" fit="true">
                                <div region="west" split="true" title="List Of Choices"
                                        style="width: 200px;height: 100%;">
					<ul class="pitem">
						<li>
						<a href="javascript:void(0)" onclick="openlink('src/profile/setDefaultTestServer.php')">Set Up Default Test Server</a>
						</li>
					</ul>
                                </div>
                                <div region="center" split="true" title="Content Panel"
                                        style="height: 100%;">
        				<div class="side2" style="">
						<iframe id="setlist" frameborder="0" scrolling="auto" style="width:100%;height:100%"></iframe>
					</div>
				</div>
			</div>
	</div>

    
</body>
</html>
