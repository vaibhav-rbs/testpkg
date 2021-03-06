<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<title>Test Depot</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
<!-- stylesheet -->
<style type="text/css">
.label2 {
    color:gray;
    font-weight:bold;
    font-size:14px;
    display:inline-block;
    width:150px;
    float:left;
    margin-top:4px;
}
</style>

<!-- jquery easy UI 1.2.4 plug ins -->
<link type="text/css" href="themes/gray/easyui.css" rel="stylesheet" />
<link type="text/css" href="themes/icon.css" rel="stylesheet" />
<link type="text/css" href="themes/main.css" rel="stylesheet" />
<link type="text/css" href="themes/testreport.css" rel="stylesheet" />
<script type="text/javascript" src="lib/1.3.3/jquery.min.js"></script>
<script type="text/javascript" src="lib/1.3.3/jquery.easyui.min.js"></script>
<script type="text/javascript" src="lib/1.3.3/datagrid-groupview.js"></script>
<script type="text/javascript" src="src/common/runMainCommon.js"></script>

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
<body class="main">    
	<div id="heading">
    	<table>
    		<tr>
    			<td><img src="img/testdepot_run_1.png" width="24" height="24" /> Test Depot</td>
    			<td align=right>
    				<img src="img/testdepot.png" width="24" height="24" 
    					 onClick="window.location.href='login.php?user_string=<?php echo $user_string?>'" /> Back to Main
    			</td>
    		</tr>
    	</table>
    </div>
    <div id="mainContainer">
    	<div id="mainTabs" class="easyui-tabs" fit=true>
			<div title="Schedule Test Job" style="padding:10px;">
	            <div id="testjobLayout" class="easyui-layout" data-options="fit:true">
	            	<div data-options="region:'north',border:false" style="height:36px;">
            			<input id=groupCombo style="width:250px;"></input>
					    <input id=masterplanCombo style="width:550px;"></input>
					    <input id=comboTestjob style="width:300px;"></input>
					    <a id=loadmasterplanBtn href="#" class="easyui-linkbutton" 
						   style="border:1px solid #D3D3D3;font-weight:bold;"
						   data-options="iconCls:'icon-download',plain:true"
						   onclick="javascript:loadTestCasesFromMasterPlan()">Load</a>
						<a href="#" id=lbtnCreateTestJob class="easyui-linkbutton" 
						   data-options="iconCls:'icon-save',plain:true"
						   style="border:1px solid #D3D3D3;font-weight:bold" 
						   onclick="javascript:saveTestJob()">Save</a>
	            	</div>
	            	<div data-options="region:'center',border:false">
	            		<table id="tcDatagrid" 
	            			   data-options="toolbar:'#tb',
	            			   			     url:'src/testjob/readTestCasesFromTemp.php?user_name=<?php echo $username?>'"></table>
	            		<div id="tb" style="height:auto;padding-left:5px;">
				            Set
				            <select id=selGroup class="easyui-combobox" panelHeight="auto" style="width:50px">
				                <option value="1" selected>1</option>
				                <option value="2">2</option>
				            </select>
				            <a href="#" class="easyui-menubutton" data-options="menu:'#mmRunBtn'">Run</a>
	            			<div id="mmRunBtn" style="width:150px;">
								<div>All</div>
								<div>None</div>
							</div>
	            		</div>
	            	</div>
	            	<div data-options="region:'south',border:false" style="height:150px;padding-top:10px;padding-left:5px;">
	            		<div style="width:50%;float:Left;">
	            			<div class=label2>Scope</div>
		            		<div>
		            			<input id="scope" class="easyui-validatebox"
		            			   data-options="required:true, validType:'maxLength[60]'"
								   style="width:400px; height:20px; border: 1px solid #D3D3D3; font-family: Arial,Helvetica,Sans-Serif; 
								   font-size: 12px;line-height:20px;padding:0px;">
			            		<a href="#" class="easyui-tooltip" data-options="
			            			position:'right',
			            			content: '<div style=color:white>Enter the scope of this test job.</div>',
			            			onShow:function() {
			            				$(this).tooltip('tip').css({
			            					backgroundColor:'#666',
			            					borderColor:'#666',
			            					height:'auto'
			            				});
			            			}
								">
			 						<img src="themes/icons/help_16.png" style="position:relative;top:3px;">
			 					</a> 	            		
		            		</div>
		            		<div class=label2>Product Hardware</div>
		            		<div>
		            			<input id="testjobProdHW" class="easyui-validatebox"
								   style="width:400px; height:20px; border: 1px solid #D3D3D3; font-family: Arial,Helvetica,Sans-Serif; 
								   font-size: 12px;line-height:20px;padding:0px;">
			            		<a href="#" class="easyui-tooltip" data-options="
			            			position:'right',
			            			content: '<div style=color:white>The name of the product being tested. For example, for Ultra VZW, ' +
			            					 'it is <b>obake_verzion</b></div>',
			            			onShow:function() {
			            				$(this).tooltip('tip').css({
			            					backgroundColor:'#666',
			            					borderColor:'#666',
			            					height:'auto'
			            				});
			            			}
								">
			 						<img src="themes/icons/help_16.png" style="position:relative;top:3px;">
			 					</a> 	            		
		            		</div>
		            		<div class=label2>Download Build?</div>
		            		<div style="height:22px;"><input id="ckDownload" type="checkbox" onchange=toggleBuildForm(this)></input></div>
		            		<div class=label2 style="clear:left;">Build Number</div>
		            		<div>
			            		<input id="testjobBuild" class="easyui-validatebox" disabled
									   style="width:400px; height:20px; border: 1px solid #D3D3D3; font-family: Arial,Helvetica,Sans-Serif; 
									   font-size: 12px;line-height:20px;padding:0px;">
								<input id="testjobBuildHidden" type=hidden></input>
			            		<a href="#" class="easyui-tooltip" data-options="
			            			position:'right',
			            			content: '<div style=color:white>The test build number to download and flash. For example, ' +
			            					 'if the tests should be run on build 17, it is <b>17</b>. if the test should be run ' +
			            					 'on latest build, <b>latest</b></div>',
			            			onShow:function() {
			            				$(this).tooltip('tip').css({
			            					backgroundColor:'#666',
			            					borderColor:'#666',
			            					width:500,
			            					height:'auto'
			            				});
			            			}
								">
			 						<img src="themes/icons/help_16.png" style="position:relative;top:3px;">
			 					</a>
			            	</div>
		            		<div class=label2>Build Url</div>
		            		<div>
		            			<input id="testjobUrl" class="easyui-validatebox" disabled
								   style="width:400px; height:20px; border: 1px solid #D3D3D3; font-family: Arial,Helvetica,Sans-Serif; 
								   font-size: 12px;line-height:20px;padding:0px;"
								   data-options="tipPosition:'right'">
								   <input id="testjobUrlHidden" type=hidden></input>
			            		<a href="#" class="easyui-tooltip" data-options="
			            			position:'right',
			            			content: '<div style=color:white>The URL where the test build can be found. For example: ' +
			            		   			 '<b>http://jenkins-main.am.mot.com/view/mkk-d-daily/job/platform_dev_obake-vzw_userdebug_mkk-d_linux_daily/</b></div>',
			            			onShow:function() {
			            				$(this).tooltip('tip').css({
			            					backgroundColor:'#666',
			            					borderColor:'#666',
			            					width:500,
			            					height:'auto'
			            				});
			            			}
								">
			 						<img src="themes/icons/help_16.png" style="position:relative;top:3px;">
			 					</a>
		            		</div>
		            		<div class=label2>Pre-setup</div>
		            		<div>
		            			<select id="profile" class="easyui-combobox" style="width:402px;">
		            				<option value="">&nbsp;</option>
		            				<option value="auto-sanity">Sanity</option>
		            				<option value="auto-stability">Stability</option>
		            				<option value="auto-regression">Regression</option>
		            				<option value="auto-gms">GMS</option>
		            			</select>
			            		<a href="#" class="easyui-tooltip" data-options="
			            			position:'right',
									content: '<div style=color:white><table cellpadding=3>' +
			            					 '<tr><th colspan=2>List of pre-setup before the test</th><th>Sanity</th><th>Stability</th><th>Regresion</th><th>GMS</th></tr>' +
			            					 '<tr><td valign=top width=1>1</td><td>Change some settings such as keep display on while charging, set display timeout to 30 minutes, disable unknown apps verification, etc.</td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td></tr>' +
											 '<tr><td valign=top width=1>2</td><td>Reboot device so the settings changed in previous app takes effect. This is necessary since the settings are changed directly in database.</td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td></tr>' +
											 '<tr><td valign=top width=1>3</td><td>Collect device property (for analysis purpose).</td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td></tr>' +
											 '<tr><td valign=top width=1>4</td><td>Wait until device camps (up to 3 minutes).</td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td></tr>' +
											 '<tr><td valign=top width=1>5</td><td>Go through the login process.</td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td>&nbsp;</td></tr>' +
											 '<tr><td valign=top width=1>6</td><td>Wait for the device to settle down.</td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td></tr>' +
											 '<tr><td valign=top width=1>7</td><td>Run script to verify device partitions (requested by memory team).</td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td></tr>' +
											 '<tr><td valign=top width=1>8</td><td>Dump database schema of all apps installed in the device (part of migration tests to catch changes in database schema).</td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td>' +
											 '<td><img src=themes/icons/checkmark_12_white.png></img></td></tr>' +
			            					 '</tr></table></div>',
			            			onShow:function() {
			            				$(this).tooltip('tip').css({
			            					backgroundColor:'#666',
			            					borderColor:'#666',
			            					width:600,
			            					height:'auto'
			            				});
			            			}
								">
			 						<img src="themes/icons/help_16.png" style="position:relative;top:3px;">
			 					</a> 
		            		</div>
						</div>
						<div style="width:50%;float:right;">
							<div class=label2 style="width:270px;">Repeats of entire list</div> 
		            		<div>
		            			<input id="testjobLoops" class="easyui-numberspinner" style="width:80px;" data-options="min:1,value:1">
			            		<a href="#" class="easyui-tooltip" data-options="
			            			position:'right',
			            			content: '<div style=color:white>Number of times to repeat the entire list of test.</div>',
			            			onShow:function() {
			            				$(this).tooltip('tip').css({
			            					backgroundColor:'#666',
			            					borderColor:'#666',
			            					height:'auto'
			            				});
			            			}
								">
			 						<img src="themes/icons/help_16.png" style="position:relative;top:3px;">
			 					</a>
		            		</div>
		            		<div class=label2 style="width:270px;">Retry for the failed tests (Optional)</div> 
		            		<div>
		            			<input id="testjobRetrySpin" class="easyui-numberspinner" style="width:80px;" data-options="min:0,value:0">
			            		<a href="#" class="easyui-tooltip" data-options="
			            			position:'right',
			            			content: '<div style=color:white>Number of times to rerun the failed tests.</div>',
			            			onShow:function() {
			            				$(this).tooltip('tip').css({
			            					backgroundColor:'#666',
			            					borderColor:'#666',
			            					height:'auto'
			            				});
			            			}
								">
			 						<img src="themes/icons/help_16.png" style="position:relative;top:3px;">
			 					</a>
		            		</div>
		            		<div class=label2 style="width:270px;">Verify RAM (Optional)</div>
							<div style="height:22px;">
								<input id=verifyRAM type=checkbox>
			            		<a href="#" class="easyui-tooltip" data-options="
			            			position:'right',
			            			content: '<div style=color:white>Do you want to check RAM size of DUT before running the test?</div>',
			            			onShow:function() {
			            				$(this).tooltip('tip').css({
			            					backgroundColor:'#666',
			            					borderColor:'#666',
			            					height:'auto'
			            				});
			            			}
								">
			 						<img src="themes/icons/help_16.png" style="position:relative;top:3px;">
			 					</a>
							</div>
							<div class=label2 style="width:270px;">Save Test Result in Test Central?</div>
							<div style="22px;">
								<input id=uploadResult type=checkbox onChange=toggleSaveOption(this)></input>
								<label id=labelCreateCyclePlan for=createCyclePlan style="color:gray;font-size:14px;font-weight:bold;padding-left:125px;display:none;">in new cycle plan?</label>
								<input id=createCyclePlan type=checkbox style="display:none;" checked></input>
							</div>
							<div id=passRateSlider style="width:300px;height:40px;padding-left:270px;padding-top:2px;display:none;">
		 						<div id="passCriteriaSlider" style="width:300px;"></div>
		 						<div id="sliderTip" style="width:340px;white-space:nowrap;padding:2px;background-color:#64ff64">
		 							Test result will be recorded as 'pass' if pass rate reaches 50%.
		 						</div>
		 					</div>
						</div>
	            	</div>
	            </div>
			</div>
			<!-- 2nd tap: select device -->
			<div title="Select Device" style="padding:10px;">
				<div id="deviceLayout" class="easyui-layout" data-options="fit:true">
					<div data-options="region:'north',border:false" style="height:36px;">
						<input id=comboTestjobRun style="width:550px;"></input>
					    <a id="lbRunNow" href="#" class="easyui-linkbutton" 
					       style="border:1px solid #D3D3D3;font-weight:bold;"
						   data-options="iconCls:'icon-play',plain:true",
						   onclick="setStartTime();runTestJob('now');">Run Now</a>
					    <a id="lbRunLater" href="#" class="easyui-linkbutton" 
				           style="border:1px solid #D3D3D3;font-weight:bold;"
					       data-options="iconCls:'icon-calendar',plain:true",
					       onclick="$('#dlgJobSchedule').dialog('open');">Run Later</a>
	            	</div>
	            	<div data-options="region:'center',border:false">
	            		<div class="easyui-layout" data-options="fit:true">
	            			<div data-options="region:'south',border:true,split:false" style="height:140px;padding:10px;">

                                <form action="" method = "" id = "execute">
                                <?php
                                $con=mysqli_connect("127.0.0.1","root","root123","testdepot");
                                if (mysqli_connect_errno()) {
                                  echo "Failed to connect to MySQL: " . mysqli_connect_error();
                                }
                                $result = mysqli_query($con,"SELECT * FROM tblDeviceRegistration");
                                echo "<table id='data-table' border='1'>
                                <tr>
                                <th>Device Serial Number</th>
                                <th>Device Time Stamp</th>
                                <th>Time</th>
                                <th>Date</th>
                                <th>NetworkType</th>
                                <th>Phone Number</th>
                                <th>Email Address</th>
                                </tr>";
                                while($row = mysqli_fetch_array($result)) {
                                echo "<tr>";
                                $deviceID = $row['deviceSerialNumber'];
                                $phoneNumber = $row['phoneNumber'];
                                //$mobileTextEmailAdd = $row['mobileTextEmailAdd'];
                                //$email = $row['phoneNumber'];
                                $email = $row['mobileTextEmailAdd'];
                                echo"<td><input type = 'radio' id = $deviceID value = $deviceID name = 'deviceID'>$deviceID</td>";
                                echo"<td>" . $row['DeviceTimeStamp'] . "</td>";
                                echo"<td>". $row['time'] . "</td>";
                                echo"<td>". $row['date'] . "</td>";
                                echo"<td>". $row['networkType'] . "</td>";
                                echo"<td>". $row['phoneNumber'] . "</td>";
                                echo"<td>". $email . "</td>";
                                echo"</tr>";
                                echo "<br>";
                                }
                                ?>
                                
                                <script type="text/javascript">
                                $(document).ready(function() {
                                
                                    $('#data-table tr').click(function() {
                                        var td = $(this).find("td");
                                        var deviceID = this.children[0].childNodes[0].id
                                        var phoneNumber = this.childNodes[5].textContent
                                        var emailAddress = this.children[6].textContent
                                    });
                                
                                });
				/*                              
                                $("input:radio[name=deviceID]").click(function() {
                                    var value = $(this).val();
                                    ajaxFunction(value);
                                });
                                */
                                $("#data-table tr").click(function() {
                                    var td = $(this).find("td");
                                    var deviceID = this.children[0].childNodes[0].id
                                    var phoneNumber = this.childNodes[5].textContent
                                    var emailAddress = this.children[6].textContent
                                    ajaxFunction(deviceID,phoneNumber,emailAddress);
                                });
                                function ajaxFunction(deviceID,phoneNumber,emailAddress){
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
                                 var user_first_name;
                                 user_first_name="<? echo $user_first_name?>";
                                 user_first_name=user_first_name.toLowerCase();
                                 queryString =  queryString + "?deviceID=" + deviceID +"&phoneNumber=" + phoneNumber +"&user_first_name="+user_first_name+"&emailAddress="+emailAddress;
                                 ajaxRequest.open("GET", "gitClone-ajax.php" + queryString, true);
                                 ajaxRequest.send(null); 
                                }
                                </script>
                                <div>Click on device corresponding radio button to execute test.</div>
                                <div id='ajaxDiv'></div>
                                </form>
			            	</div>
			            	<div data-options="region:'center',border:true">
			            		<table id="deviceDatagrid">
			            			<thead>
								        <tr>
								            <th data-options="field:'title'">Click client below to display devices</th>
								        </tr>
								    </thead>
			            		</table>
			            	</div>
	            		</div>
	            	</div>
	            	<div data-options="region:'east', border:true, split:true" style="width:500px;">
	            		<div class="easyui-layout" data-options="fit:true">
	            			<!-- 
	            			<div id="testjobName" data-options="region:'north',border:false" style="padding:5px;background-color:#D3D3D3">
	            				Test Jobs
	            			</div>
	            			-->
	            			<div data-options="region:'south',border:false,split:true" style="height:300px;">
	            				<table id="logDg"></table>
	            				
	            			</div>
	            			<div data-options="region:'center',border:false">
	            				<table id="jobQueueDg">
			            			<thead>
								        <tr>
								            <th data-options="field:'title'">Click device on the left to display job queue</th>
								        </tr>
								    </thead>
			            		</table>
	            			</div>
	            			<div data-options="region:'north', border:false, split:true" style="height:150px;">
	            				<table id="dgSettings"></table>
	            			</div>
	            		</div>
	            	</div>
				</div>
			</div>
			<!-- 3rd tap: check the test result -->
			<div title="Monitor Test result" style="padding:10px;">
				<div id="monitorLayout" class="easyui-layout" data-options="fit:true">
		    		<div data-options="region:'north',border:false" style="height:36px;">
						<!-- 
						<input id=groupCombo1 style="width:250px;"></input>
						<input id=masterplanCombo1 style="width:550px;"></input>
						<input id=testreportCombo style="width:500px;"></input>
						-->
						<input id=masterplanCombo1 style="width:550px;"></input>
						<select id="cgTestResult" style="width:550px;"></select>
		    		</div>
		    		<div data-options="region:'center',border:false">
		    			<table id="dgTestResult"></table>
		    		</div>
		    		<div data-options="region:'east',border:true,split:true,minWidth:400" style="width:400px;padding:10px;">
		    			<div id="content"></div>
		    		</div>
		    	</div>
			</div>
		</div>
    </div>
    
    <!-- dialog box for job schedule -->
    <div id="dlgJobSchedule" class="easyui-dialog" style="width:465px;height:310px;padding:10px;">
		<div class=label2>Starts On</div>
		<div>
			<input class="easyui-datetimebox" id="startDate" data-options="showSeconds:false" value="" style="width:200px;">
			<input id=ckRepeat type=checkbox onchange="showRepeat(this)" />
			<font color=gray size="2"><b>Repeat?</b></font>
			<script type="text/javascript">
				function showRepeat(element) {
					if (element.checked) {
						$('#repeat').show();
					} else {
						$('#repeat').hide();
						$('#repeatCC').combobox('select', 'Daily');
						$('#repeatEveryCC').combobox('select', '1');
						$('#endOnCC').combobox('select', 'After');
						$('#occurrenceSS').numberspinner('setValue', 1);
					}
				}
			</script>
		</div>    	 
		<div id=repeat style="display:none;">
			<div class=label2>Repeats</div>
			<div>
				<select id="repeatCC" class="easyui-combobox" style="width:200px;">
					<option value="Daily">Daily</option>
		            <option value="Weekly">Weekly</option>
		            <option value="Monthly">Monthly</option>
				</select>
			</div>
			<div class=label2>Repeat Every</div>
			<div>
				<select id="repeatEveryCC" class="easyui-combobox" style="width:200px;">
            		<option value="1">1</option>
            		<option value="2">2</option>
            		<option value="3">3</option>
            		<option value="4">4</option>
            		<option value="5">5</option>
            		<option value="6">6</option>
            		<option value="7">7</option>
            		<option value="8">8</option>
            		<option value="9">9</option>
            		<option value="10">10</option>
            		<option value="11">11</option>
            		<option value="12">12</option>
            		<option value="13">13</option>
            		<option value="14">14</option>
            		<option value="15">15</option>
            		<option value="16">16</option>
            		<option value="17">17</option>
            		<option value="18">18</option>
            		<option value="19">19</option>
            		<option value="20">20</option>
            		<option value="21">21</option>
            		<option value="22">22</option>
            		<option value="23">23</option>
            		<option value="24">24</option>
            		<option value="25">25</option>
            		<option value="26">26</option>
            		<option value="27">27</option>
            		<option value="28">28</option>
            		<option value="29">29</option>
            		<option value="30">30</option>
            	</select>
            </div>
			<div id=repeatOn style="display:none;">
				<br>
         		<input id=ckSun type=checkbox>Sun
            	<input id=ckMon type=checkbox>Mon
            	<input id=ckTue type=checkbox>Tue
            	<input id=ckWed type=checkbox>Wed
            	<input id=ckThur type=checkbox>Thur
            	<input id=ckFri type=checkbox>Fri
            	<input id=ckSat type=checkbox>Sat
            	<br></br>
			</div>
			<div class=label2>Ends</div>
			<div>
				<select id="endOnCC" class="easyui-combobox" style="width:200px;">
					<option value="After">After</option>
					<option value="Never">Never</option>
					<option value="On">On</option>
            	</select>
            	<div id=divOccurrence style="margin-left:150px;">
            		<input id="occurrenceSS" class="easyui-numberspinner" data-options="min:1,value:1" style="width:200px;">
            	</div>
            	<div id=divEndDate style="display:none;margin-left:150px;">
            		<input type=hidden class="easyui-datetimebox" id="endDate" data-options="showSeconds:false" value="" style="width:200px;">
            	</div>
            </div>
			<div class=label2 style="float:none;">Repeat Summary</div>
			<p id=repeatSummary style="padding-left:10px;"></p>
		</div>    	 
	</div>
    <div id=bb>
    	<a href="#" class="easyui-linkbutton" 
           style="border:1px solid #D3D3D3;font-weight:bold;"
		   data-options="plain:true",
		   onclick="$('#dlgJobSchedule').dialog('close');runTestJob('later');">Start</a>
	   	<a href="#" class="easyui-linkbutton" 
           style="border:1px solid #D3D3D3;font-weight:bold;"
	       data-options="plain:true",
	       onclick="javascript:$('#dlgJobSchedule').dialog('close');">Cancel</a>
    </div>
    
    <!-- datagrid context menu -->
    <div id="mm" class="easyui-menu" style="width:120px;">
    	<div id="m-delete" data-options="iconCls:'icon-delete'">Delete</div>
    	<div data-options="iconCls:'icon-view'">View Status</div>
    	<div data-options="iconCls:'icon-view'">View Detail</div>
    </div>
    
    <!-- dialog for settings -->
    <div id="dlgSettings" class="easyui-dialog" style="width:600px;height:400px;">
		<table id="dgFileBrowser"></table>
    </div>
    <div id=bbSettings>
    	<a href="#" class="easyui-linkbutton" 
           style="border:1px solid #D3D3D3;font-weight:bold;"
		   data-options="plain:true",
		   onclick="$('#dlgSettings').dialog('close');saveFilePath('#dgSettings');">OK</a>
	   	<a href="#" class="easyui-linkbutton" 
           style="border:1px solid #D3D3D3;font-weight:bold;"
	       data-options="plain:true",
	       onclick="javascript:$('#dlgSettings').dialog('close');">Cancel</a>
    </div>
</body>
</html>

