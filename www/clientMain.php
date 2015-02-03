<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
<title>Test Depot</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
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
</style>

<!-- jquery easy UI 1.2.4 plug ins -->
<link type="text/css" href="themes/gray/easyui.css" rel="stylesheet" />
<!-- <link type="text/css" href="themes/default/treeModule.css" rel="stylesheet" /> -->
<link type="text/css" href="themes/icon.css" rel="stylesheet" />
<!-- <link type="text/css" href="themes/menu.css" rel="stylesheet" />  -->
<link type="text/css" href="themes/main.css" rel="stylesheet" />
<link type="text/css" href="themes/radiobuttons.css" rel="stylesheet" />
<link type="text/css" href="themes/fileOpen.css" rel="stylesheet" />
<link type="text/css" href="src/testscript/testscript.css" rel="stylesheet" />
<script type="text/javascript" src="lib/1.3.3/jquery.min.js"></script>
<script type="text/javascript" src="lib/1.3.3/jquery.easyui.min.js"></script>
<script type="text/javascript" src="src/common/common.js"></script>
<script type="text/javascript" src="src/testscript/testscript.js"></script>
<!-- <script type="text/javascript" src="src/execution/execution.js"></script> -->
<script type="text/javascript" src="src/runlist/js_functions.js"></script>
<script type="text/javascript" src="src/testplan/testplan.js"></script>
<script type="text/javascript" src="src/report/report.js"></script>
<!-- script includes all the common JS functions -->


<!-- script includes RGraph -->
<script src="lib/RGraph/libraries/RGraph.common.core.js"></script>
<script src="lib/RGraph/libraries/RGraph.common.adjusting.js"></script> <!-- Just needed for adjusting -->
<script src="lib/RGraph/libraries/RGraph.common.annotate.js"></script>  <!-- Just needed for annotating -->
<script src="lib/RGraph/libraries/RGraph.common.context.js"></script>   <!-- Just needed for context menus -->
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

<?php 
	header('Content-type: text/html; charset=utf-8');
	// To clean up json file, so that empty datagrid loads initially .
    $datagrid_file="/var/www/tempdata/datagrid_data.json";
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
    <!-- Context Menu -->
	<div id="treeTestCaseMenu" class="easyui-menu" style="width:120px;">
		<div id=m-new data-options="name:'new',iconCls:'icon-new'">New</div>
		<div id=m-refresh data-options="name:'refresh',iconCls:'icon-reload'">Refresh</div>
		<div id=m-download data-options="name:'download',iconCls:'icon-download'">Download</div>
		<div id=m-upload data-options="name:'upload',iconCls:'icon-upload'">Upload</div>
	</div>
	<div id="tcNavTreeMenu" class="easyui-menu" style="width:120px;">
		<div id=m-add data-options="name:'add',iconCls:'icon-add'">Add</div>
	</div>
	<div id="heading">
    	<table>
    		<tr>
    			<td><img src="img/testdepot_share_1.png" width="24" height="24" /> Test Depot</td>
    			<td align=right>
    				<img src="img/testdepot.png" width="24" height="24" 
    					 onClick="window.location.href='login.php?user_string=<?php echo $user_string?>'" /> Back to Main
    			</td>
    		</tr>
    	</table>
    </div>
    <div id="mainContainer">
    	<div id="mainTabs" class="easyui-tabs" fit=true>
			<div title="Develop Test Case" style="padding:10px;">
	            <div id="layoutScript" class="easyui-layout" style="width:100%;height:100%;">
	            	<div data-options="region:'north',border:false" style="height:36px;padding-left:5px;">
	            		<div style="float:left;">
	            			<div id=tcNameDev class=label>Test Suite/Case</div>&nbsp;&nbsp;
							<a id=savelbtn href="#" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true"
						    style="border:1px solid #D3D3D3;font-weight:bold;display:none">Save</a>
						    <a id=rowAddlbtn href="#" class="easyui-linkbutton" data-options="iconCls:'icon-row-add',plain:true"
						    style="border:1px solid #D3D3D3;font-weight:bold;display:none" onclick="javascript:addRow()">Row Add</a>
						    <a id=rowDellbtn href="#" class="easyui-linkbutton" data-options="iconCls:'icon-row-delete',plain:true"
						    style="border:1px solid #D3D3D3;font-weight:bold;display:none" onclick="javascript:removeRow()">Row Delete</a>
						    <!-- <input type="file" id="fileselect" name="fileselect[]" /> -->
	            		</div>
						<div style="float:right;">
							<div class="field switch">
								<input type="radio" id="radioDevSuite" name="field" checked />
								<input type="radio" id="radioDevPlan" name="field" />
								<label for="radioDevSuite" class="cb-enable selected"
									   onclick="javascript:showSuiteTree('#searchDev', '#treeTestCase', '#searchTreeTestCase')">
									   <span>Suite</span>
								</label>
								<label for="radioDevPlan" class="cb-disable"
									   onclick="javascript:showPlanTree('#searchDev', '#treeTestCase', '#searchTreeTestCase')">
									   <span>Plan</span>
								</label>
							</div>
						</div>
						<div style="float:right;width:220px;height:30px;margin-top:5px;">
							<input id="searchDev">
						</div>
			        </div>
	                <div data-options="region:'west',split:true,minWidth:260" style="width:260px;">
	                	<ul id="treeTestCase" style="padding:5px;" class="easyui-tree" url="src/testscript/testscripttreeBySuite.php?user_name=<?php echo $username?>"></ul>
	                	<ul id="searchTreeTestCase" style="padding:5px;display:none;" class="easyui-tree" url="src/testscript/searchTestSuites.php"></ul>    
	                </div>   
	                <div region="center" style="padding:0">
	                	<div id=message></div>
	                	<div id=description class=title style=display:none onclick="javascript:setEdit(this)" 
	                		 onBlur="javascript:setReadOnly(this)"></div>
	                	<div id=content class=pagestyle style=display:none>
	                		<div id=table></div>
	                		<div id=table1></div>
	                	</div>
	                	<div id=scriptDiv style="padding:10px;display:none;">
	                		<div id=gitLabel class=label>Git Location</div>
	                		<div>
		                		<input id="cbGitSelect" class="easyui-combobox" style="width:500px;"
		                			   data-options="fit:true,valueField:'id',textField:'text',url:'src/testscript/getGitPaths.php'"></input>
	                		</div>
	                		<br>
	                		<div id=scriptLabel class=label>Script Location</div>
	                		<div>
	                			<div id=scriptLoc 
	                				 style="width:auto;height:20px;
	                				 		white-space:nowrap;
  											border:solid 1px #D3D3D3;
											vertical-align:middle;
  											line-height: 20px;
  											padding: 0px 2px;"
	                			 	 onclick="javascript:setEdit(this)" 
	                			     onBlur="javascript:setReadOnly(this)">
	                			</div>
	                		</div>
	                		<!-- <div id=filedrag style="border:dashed 1px;float:right;">drop test script file here</div> -->
	                	</div>
	                	<div id=testcasetable style="display:none;border-top:2px solid #B1B2B3;">
	                		<table id="tcDetailTable" class="easyui-datagrid"></table>
	                	</div>
	                </div>
				</div>
			</div>
			<div title="Plan Test" style="padding: 10px;">
				<div id=layoutTPContainer>
					<div data-options="region:'north',border:false" style="height:36px;">
						<div style="float:left;">
							<input id="workingPlan">
							<a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-new',plain:true"
				           	style="border:1px solid #D3D3D3;font-weight:bold" onclick="$('#dlg').dialog('open');">New</a>
							<a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true"
				           	style="border:1px solid #D3D3D3;font-weight:bold" onclick="javascript:save_local_plan_tests()">Save</a>
				           	<a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-upload',plain:true"
				           	style="border:1px solid #D3D3D3;font-weight:bold" onclick="javascript:submit_tc_plan_tests()">Submit</a>
				           	<a href="javascript:void(0)" id="sbRemove" class="easyui-splitbutton"
				           	   style="border:1px solid #D3D3D3;font-weight:bold"
				           	   data-options="menu:'#sbRemoveMenu', iconCls:'icon-trash'">Remove</a>
				           	<div id="sbRemoveMenu" style="width:100%;font-weight:bold;">
				           		<div onclick="javascript:remove_local_plan()">Test Plan</div>
				           		<div onclick="javascript:remove_test()">Test Case</div>
				           	</div>
						</div>
						<div style="float:right;">
							<div class="field switch">
								<input type="radio" id="radioPlanSuite" name="field" checked />
								<input type="radio" id="radioPlanPlan" name="field" />
								<label for="radioPlanSuite" class="cb-enable selected"
									   onclick="javascript:showSuiteTree('#searchPlan', '#tcNavTree', '#searchTcNavTree')">
									   <span>Suite</span>
								</label>
								<label for="radioPlanPlan" class="cb-disable" 
									   onclick="javascript:showPlanTree('#searchPlan', '#tcNavTree', '#searchTcNavTree')">
									   <span>Plan</span>
								</label>
							</div>
						</div>
						<div style="float:right;width:220px;height:30px;margin-top:5px;">
							<input id="searchPlan">
						</div>
			        </div>
					<div data-options="region:'west',split:true,minWidth:260" style="width:260px;">
	                	<ul id="tcNavTree" style="padding:5px;" url="src/testscript/testscripttreeBySuite.php?user_name=<?php echo $username?>"></ul>
	                	<ul id="searchTcNavTree" style="padding:5px;display:none;" class="easyui-tree" url="src/testscript/searchTestSuites.php"></ul>
		            </div>
					<div data-options="region:'center'">
						<table id="testplanDatagrid" class="easyui-datagrid" url="tempdata/testcase.json"></table>
					</div>
				</div>
			</div>
			<div title="Update Test Result" style="padding: 10px;">
				<div id="layoutExec" class="easyui-layout" style="width:100%; height:100%;">
					<div data-options="region:'north',border:false" style="height:36px;padding-left:5px;">
						<div style="float:left;">
							<div id="tpNameExec" class=label>Test Plan</div>
							<a href="#" class="easyui-linkbutton" data-options="iconCls:'icon-save',plain:true"
						       style="border:1px solid #D3D3D3;font-weight:bold" onclick="javascript:saveTestResults()">Submit</a>
						</div>
						<div style="float:right;margin-top:5px;">
							<input id="searchResult">
						</div>
			        </div>
					<div data-options="region:'west', split:true, minWidth:260" style="width:260px;">
						<ul id="treeTestPlan" style="padding:5px;" url="src/runlist/loadPlanData.php?user_name=<?php echo $username?>"></ul>
						<ul id="searchTreeTestPlan" style="padding:5px;" url="src/runlist/searchLoadPlanData.php"></ul>
					</div>
					<div region="center" data-options="border:true">
						<div class="easyui-layout" data-options="fit:true">
							<div data-options="region:'center',border:false">
								<table id="tcExecDatagrid" data-options="toolbar:'#exec-tb',
									   url:'src/runlist/readTestPlanCases.php?user_name=<?php echo $username?>'">
								</table>
								<div id="exec-tb" style="height:auto;padding-left:5px;">
								 	<span>Update result: </span>
									<select id="selResult" class="easyui-combobox" panelHeight="auto">
										<option value="P">P</option>
										<option value="F">F</option>
										<option value="B">B</option>
										<option value="I">I</option>
									</select> 
								</div>
							</div>
							<div id="layoutExec_south" data-options="region:'south',split:true,border:false,minHeight:250"
		    					 style="width:250px;padding:10px;">
		    				</div>
						</div>
    				</div>
    			</div>
			</div>
			<!-- 
			<div title="Report Test Result" style="padding: 10px;">
				<div id="layoutReport" class="easyui-layout" style="width: 100%; height: 100%;">
					<div region="west" split="true" style="width: 500px;height: 100%;">
						<ul id="treeTestPlanReport" class="easyui-tree" style="padding:5px;" url="src/report/loadPlanData_report.php?user_name=<?php echo $username?>"></ul>
					</div>
					<div region="center" split="true" style="width: 700px; height: 100%;">
						<div id="summary" style="padding:5px;">
							<p id ="cycle_name" class="p1"></p>
							<p id ="build_id" class="p1"></p>
							<p id ="create_date" class="p1"></p>
							<p id ="exec_date" class="p1"></p>
						</div>
	 					<div id="graph_overall" style="padding:5px;">
							<p id = "g1" class="p1"></p>
							<canvas id="myCanvas1" width="800" height="600">[No canvas support]</canvas> 
						</div>
	 					<div id="graph_bycomponent" style="padding:5px;">
							<p id = "g2" class="p1"></p>
							<canvas id="myCanvas2" width="800" height="600">[No canvas support]</canvas>
						</div>
	 					<div id="detail_result" style="padding:5px;">
							<a name="original"></a> 
							<table id="detail_result_table" class="stylesample" border="1" width="100%"></table>
						</div>
	 					<div id="defect_info" style="padding:5px;">
							<p id = "defect_summary" class="p1"></p>
						</div>
						<div>
							<table id="defect_summary_table" style="padding:5px;" class="stylesample" border="1" width="60%"></table>
						</div>
						<div>
							<br/><br/>
							<table id="defect_detail_table" style="padding:5px;" class="stylesample" border="1" width="100%"></table>
						</div>
	 					<div id="detail_info_block" style="padding:5px;"></div>
					</div>
				</div>
			</div>
			-->
		</div>
    </div>
    
    <!-- Create new test plan dialog box -->
    <div id="dlg" class="easyui-dialog" title="Create New Test Plan" data-options="iconCls:'icon-new', closed:true, resizable:true"
    	 style="width:750px;height:400px;padding:10px;">
    	 <div class="easyui-layout" data-options="fit:true, border:false">
    	 	<div data-options="region:'north', split:false, border:false" style="height:40px;padding:5px;">
				<div class="field switch">
					<input type="radio" id="radio1" name="field" checked />
					<input type="radio" id="radio2" name="field" />
					<label for="radio1" class="cb-enable selected" onclick="refreshDisplay('Mplan');">
						   <span>Master Plan</span>
					</label>
					<label for="radio2" class="cb-disable" onclick="refreshDisplay('Cplan');">
						   <span>Cycle Plan</span>
					</label>
    	 		</div>
    	 	</div>
    	 	<div data-options="region:'center', border:false">
				<div id="masterSection" style="padding:10px;">
					<div class=label style="width:120px;">Select Group</div>
					<div style="height:25px;"><input id="groupCombo"></div>
					<div class=label style="width:120px;">Select Product</div>
					<div style="height:25px;"><input id="productCombo"></div>
					<div class=label style="width:120px;">Scope</div>
					<div style="height:25px;">
						<input id=scope name="ttaa" class="combo-text validatebox-text" 
							   style="width:700px; height:20px; border: 1px solid #D3D3D3; font-family: Arial,Helvetica,Sans-Serif; 
							   font-size: 12px;line-height:20px;padding:0px;">
					</div>
					<div class=label style="width:120px;">Start Date</div>
					<div style="height:25px;"><input id=startDate class="easyui-datebox"></div>
					<div class=label style="width:120px;">End Date</div>
					<div style="height:25px;"><input id=endDate class="easyui-datebox"></div>
					<div align=middle style="height:50px;">
						<a href="#" id=createLinkbutton class="easyui-linkbutton" 
						   data-options="iconCls:'icon-notepad',plain:true"
						   style="border:1px solid #D3D3D3;font-weight:bold;margin-top:22px;" 
						   onclick="javascript:create_local_master_plan();$('#dlg').dialog('close');">Create</a>
					</div>
				</div>
				<div id="cycleSection" style="display:none;padding:10px;">
					<div class=label style="width:120px;">Select Group</div>
					<div style="height:25px;"><input id="groupCombo2"></div>
					<div class=label style="width:120px;">Select Master Plan</div>
					<div style="height:25px;"><input id="masterCombo"></div>
					<div class=label style="width:120px;">Start Date</div>
					<div style="height:25px;"><input id=startDate2 class="easyui-datebox"></div>
					<div class=label style="width:120px;">End Date</div>
					<div style="height:25px;"><input id=endDate2 class="easyui-datebox"></div>
					<div align=middle style="height:50px;">
						<a href="#" id=createLinkbutton2 class="easyui-linkbutton" 
						   data-options="iconCls:'icon-notepad',plain:true"
						   style="border:1px solid #D3D3D3;font-weight:bold;margin-top:22px;" 
						   onclick="javascript:create_local_cycle_plan();$('#dlg').dialog('close');">Create</a>
					</div>
				</div>
    	 	</div>
    	 </div>
   	</div>
   	
   	<!-- dialog for script function select -->
   	<div id="dlgScriptFuncSelect" class="easyui-dialog" title="Test Cases" style="width:600px;height:300px;"
   		 data-options="modal:true,resizable:true,closed:true">
   		<!-- <div id="aaFunctions" class="easyui-accordion" data-options="fit:true,multiple:true"></div> -->
   		<table id="dgFuncSelect">
   		</table>
   	</div>
</body>
</html>
