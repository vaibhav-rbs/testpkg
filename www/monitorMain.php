<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html lang="en">
<head>
<title>Test Depot</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >

<!-- jquery easy UI 1.2.4 plug ins -->
<link type="text/css" href="themes/gray/easyui.css" rel="stylesheet" />
<link type="text/css" href="themes/icon.css" rel="stylesheet" />
<link type="text/css" href="themes/main.css" rel="stylesheet" />
<script type="text/javascript" src="lib/1.3.3/jquery.min.js"></script>
<script type="text/javascript" src="lib/1.3.3/jquery.easyui.min.js"></script>
<script type="text/javascript" src="src/common/monitorMainCommon.js"></script>
<script type="text/javascript" src="https://apis.google.com/js/client.js"></script>

<!-- script includes RGraph -->
<script src="lib/RGraph/libraries/RGraph.common.core.js"></script>
<script src="lib/RGraph/libraries/RGraph.common.annotate.js"></script>  <!-- Just needed for annotating -->
<script src="lib/RGraph/libraries/RGraph.common.context.js"></script>   <!-- Just needed for context menus -->
<script src="lib/RGraph/libraries/RGraph.common.dynamic.js"></script>   <!-- Just needed for event -->
<script src="lib/RGraph/libraries/RGraph.common.resizing.js"></script>  <!-- Just needed for resizing -->
<script src="lib/RGraph/libraries/RGraph.common.tooltips.js"></script>  <!-- Just needed for tooltips -->
<script src="lib/RGraph/libraries/RGraph.common.zoom.js"></script>      <!-- Just needed for zoom -->
<script src="lib/RGraph/libraries/RGraph.bar.js"></script>              <!-- Just needed for bar charts -->
<script src="lib/RGraph/libraries/RGraph.bipolar.js"></script>          <!-- Just needed for bi-polar charts -->
<script src="lib/RGraph/libraries/RGraph.funnel.js"></script>           <!-- Just needed for funnel charts -->
<script src="lib/RGraph/libraries/RGraph.gantt.js"></script>            <!-- Just needed for gantt charts -->
<script src="lib/RGraph/libraries/RGraph.hbar.js"></script>             <!-- Just needed for horizontal bar charts -->
<script src="lib/RGraph/libraries/RGraph.hprogress.js"></script>        <!-- Just needed for horizontal progress bars -->
<script src="lib/RGraph/libraries/RGraph.led.js"></script>              <!-- Just needed for LED charts -->
<script src="lib/RGraph/libraries/RGraph.line.js"></script>             <!-- Just needed for line charts -->
<script src="lib/RGraph/libraries/RGraph.meter.js"></script>            <!-- Just needed for meter charts -->
<script src="lib/RGraph/libraries/RGraph.odo.js"></script>              <!-- Just needed for odometers -->
<script src="lib/RGraph/libraries/RGraph.pie.js"></script>              <!-- Just needed for pie AND donut charts -->
<script src="lib/RGraph/libraries/RGraph.rose.js"></script>             <!-- Just needed for rose charts -->
<script src="lib/RGraph/libraries/RGraph.rscatter.js"></script>         <!-- Just needed for rscatter charts -->
<script src="lib/RGraph/libraries/RGraph.scatter.js"></script>          <!-- Just needed for scatter charts -->
<script src="lib/RGraph/libraries/RGraph.vprogress.js"></script>        <!-- Just needed for vertical progress bars -->

<!-- script includes all the common JS functions -->
<?php header('Content-type: text/html; charset=utf-8');
	// To clean up json file, so that empty datagrid loads initially .
    $datagrid_file="./tempdata/datagrid_data.json";
    $fh1=fopen($datagrid_file,'w') or die("cant open file");
    fwrite($fh1,"");
    fclose($fh1);

    include 'src/common/define_properties.php';
    require_once 'SOAP/Client.php';

    $user_string = $_REQUEST['user_string'];
    list($username, $user_first_name, $user_last_name) = preg_split("/\^/", $user_string);
    
    session_start();
?>

<script type="text/javascript">
    var username;
    username="<? echo $username?>";
    var user_first_name;
    user_first_name="<? echo $user_first_name?>";
    var username;
    user_last_name="<? echo $user_last_name?>";
    var user_string = username + ":" + user_first_name + ":" + user_last_name; 
</script>

</head>

<body class="main" onload="authorize();">    
	<div id="heading" style="position:fixed;margin:0,padding:0;top:0;left:0;right:0;z-index:999">
    	<table>
    		<tr>
    			<td><img src="img/testdepot_dashboard_1.png" width="24" height="24" /> Test Depot</td>
    			<td align=right>
    				<img src="img/testdepot.png" width="24" height="24" 
    					 onClick="window.location.href='login.php?user_string=<?php echo $user_string?>'" /> Back to Main
    			</td>
    		</tr>
    	</table>
    </div>
    <div id=menuBar style="position:relative;top:42px;padding:10px;">
		<?php 
		if($_SESSION['access_token'] != '') {
			?>
			<button id="queryButton" onclick="runBigquery();">Run Bigquery</button>
			<?php 
		} else {
			?>
			<a id="authLink" href=''><button>Connect to Google API Service</button></a>
			<?php 
		}
		?>
    </div>
    <div id=canvas style="position:relative;top:46px;padding:10px;display:inline-block;float:left;">
    	<canvas id="cvs" width=600, height=400>[No canvas support]</canvas>
    </div>
</body>
</html>