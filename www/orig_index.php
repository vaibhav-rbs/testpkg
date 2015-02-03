<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd"
    >
<html lang="en">
<head>
    <title>Test Depot Log On</title>
    <?php header('Content-type: text/html; charset=utf-8');
    require_once 'src/common/define_properties.php';
    $err_message = htmlspecialchars($_GET['err_message']);
    $user_string = htmlspecialchars($_GET['user_string']);
   
    if ($user_string){ 
    	list($username, $user_first_name, $user_last_name) = preg_split("/\^/", $user_string);
    }else{
	$user_string = $_COOKIE["testdepot_user_str"];
    	list($username, $user_first_name, $user_last_name) = preg_split("/\^/", $user_string);
    }

    
    ?>
    <link type="text/css" href="themes/main.css" rel="stylesheet" />
    <style type="text/css">
    	.container {
    		position:relative;
    		height:100%;
    	}
    	
    	.content {
    		position:absolute;
    		bottom:0;
    		left:0;
    		top:41px;
    		right:0px;
    	}
    	
    	.header {
    		height: 41px;
			background-color:gray;
    	}
    </style>
    <script type="text/javascript">
		function menuOption(option) {
	       	
	       	var header = '';
	       	var descripition = '';

	       	switch(option) {
		       	case 'dashboard':
			       	header = 'Monitoring';
			       	description = "See what's happening on SW quality";
			       	break;
		       	case 'share':
			       	header = 'Sharing';
			       	description = "Share test case or test scripts";
			       	break;
		       	case 'run':
			       	header = 'Running';
			       	description = "Run test scripts remotely or locally";
			       	break;
	       	default:
		       	header = 'Test Depot';
	       		description = "Choose menu options";
	       		break;
	       	}
	       		       	
	       	document.getElementById('menuHeader').innerHTML = header;
			document.getElementById('menuDescription').innerHTML = description;
	       	
	       	/*
	       	if (visible) {
	       		e.style.display = "block";
	       	} else {
		       	e.style.display = "none";
	       	}*/
	    }

	    function redirect(url) {
		    var username = "<?php echo $username?>";

		    if (username.length > 0) {
		    	window.location = url;    
		    } else {
			    alert("Please log on first!");
		    }
	    }
    </script>
</head>
<body class="main">
	<div class="container">
		<div id="heading" class="header">
			<table>
	    		<tr>
	    			<td><img src="img/testdepot.png" width="24" height="24" /> Test Depot</td>
	    			<td align=right style="color:red"><?php echo $err_message?></td>
	    			<td align=right style="width:600px">
	    				<?php
	    				if (strlen($username) > 0) {
	    					echo "Logged In as " . $user_first_name . " " . $user_last_name . " (" . $username . ")";
	    				} else {
	    					echo '<form method="post" action="authentication.php">
									Core ID <input type="text" id="coreid" name = name />
									&nbsp;&nbsp;&nbsp;
									Password <input type="password" id="pwd" name=password />
									<input type="submit" value="Log On">
								  </form>';
	    				}
	    				?>	
	    			</td>
	    		</tr>
	    	</table>
		</div>
		<div class="content">
			<div style="float:left;width:70%;height:100%;">
				<div style="width:324px;height:375px;
							position:relative;top:50%;margin-top:-188px;margin-left:auto;margin-right:auto;
							padding-top:10px;">
					<div onmouseover="javascript:menuOption('dashboard')" 
			    	     onmouseout="javascript:menuOption('default')"
			    	     onclick="javascript:redirect('monitorMain.php?user_string=<?php echo $user_string?>')"
			    	     style="position:relative;">
						<img src="img/testdepot_dashboard.png" />
					</div> 
					<div onmouseover="javascript:menuOption('share')" 
				    	 onmouseout="javascript:menuOption('default')"
				       	 onclick="javascript:redirect('clientMain.php?user_string=<?php echo $user_string?>')"
				       	 style="position:relative;margin-top:-100px;">
						<img src="img/testdepot_share.png" />
					</div>
					<div onmouseover="javascript:menuOption('run')" 
				    	 onmouseout="javascript:menuOption('default')"
				         onclick="javascript:redirect('runMain.php?user_string=<?php echo $user_string?>')"
				         style="position:relative;margin-top:-285px;margin-right:2px;float:right;">
						<img src="img/testdepot_run.png" />
					</div>
				</div>
			</div>
			<div style="float:right;width:30%;height:100%;">
				<div class=label style="text-align:center;position:relative;top:50%;height:100px;margin-top:-50px;">
			    	<h1 id=menuHeader>Test Depot</h1>
			    	<p id=menuDescription>Choose menu options</p>
				</div>
   			</div>
		</div>
	</div>
</body>
</html>
