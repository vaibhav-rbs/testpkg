// global variable
var curPosition;
var curPageNum;
var globalGroup;
var globalPlan;
var weekday = new Array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
var curInterval = undefined;
var logParseInterval = undefined;
var editIndex = undefined;
var curClientId = undefined;

$(window).resize(function() {
	fitWindowSize();
});

$(document).ready(function() {
	$('#groupCombo').combobox({
		valueField:'id', 
		textField:'text', 
		url:'src/testmonitor/get_group.php?user_name='+username,
        onSelect : function() {
            var group = $('#groupCombo').combobox('getValue');
            
            if ( group == "--Select group--" || group == "default" || group ==null){
            	
            } else {
            	var win = $.messager.progress({
                           	text:'Loading master plan...',
                           	interval:'900'
                   	});
                
            	$.post('src/testjob/get_master_plan.php',{group:group},function(result){
            		if (result.msg == "Empty info from Test Central") {
            			alert(result.msg);
            		} else {
            			$('#masterplanCombo').combobox('loadData', result);
            		}
            		$.messager.progress('close');
            	},'json');
            }
		},
		onLoadSuccess:function() {
			/***********************************************************************************
			$.ajaxSetup({cache: false});
			$.get('src/testjob/read_tp_testjob_list.php', {user_name:username}, function(result) {
				var jsonArr = JSON.parse(result);
				group = jsonArr["group"].replace('(', '').replace(')', '');
				plan = jsonArr["plan"];
				
				$('#groupCombo').combobox('select', group);
			});
			//$('#groupCombo').combobox('select', globalGroup);
			
			// load master plan based on the selected group
			var group = $('#groupCombo').combobox('getText');
			
			if (group) {
				$.get('src/testjob/get_master_plan_from_temp.php?username=' + username, function(result) {
					$('#masterplanCombo').combobox('setValue', result);
					$('#masterplanCombo').combobox('setText', result);
				});
			}
			**************************************************************************************/
		},
		onCheck:function() {
			
		}
	});
	
	$('#masterplanCombo').combobox({
		valueField:'id',
		textField:'text',
		onLoadSuccess:function() {
			/*$.ajaxSetup({cache: false});
			$.get('src/testjob/read_tp_testjob_list.php', {user_name:username}, function(result) {
				var jsonArr = JSON.parse(result);
				group = jsonArr["group"].replace('(', '').replace(')', '');
				plan = jsonArr["plan"];
				
				$('#masterplanCombo').combobox('select', plan);
			});*/
			//$('#masterplanCombo').combobox('select', globalPlan);
		},
		onSelect:function() {
            var masterPlan = $(this).combobox('getValue');
            
            if (masterPlan == "--Select master plan--" || masterPlan == "default" || masterPlan == null) {
            	
            } else {
            	var win = $.messager.progress({
                           	text:'Loading test jobs...',
                           	interval:'900'
                   	});
                
            	$.post('src/testjob/get_testjob.php', {plan:masterPlan} ,function(result){
            		if (result.msg == "Empty info from Test Central") {
            			alert(result.msg);
            		} else {
            			try {
            				var options = JSON.parse(result);
            				$('#comboTestjob').combobox({
            					data:options
            				});
            			} catch (e) {
            				alert(e);
            			}
            		}
            		$.messager.progress('close');
            	});
            }
		}
	});
	
	$('#comboTestjob').combobox({
		valueField:'id',
		textField:'text',
		onSelect:function(record) {
			if (record.text != '--Select test job--') {
				readTestJob(record.text);
			}
		},
		onShowPanel:function() {
			var masterPlan = $('#masterplanCombo').combobox('getValue');
			$(this).combobox('reload', 'src/testjob/get_testjob.php?plan=' + masterPlan);
		}
	});
	
	$('#comboTestjobRun').combobox({
		valueField:'id',
		textField:'text',
		onShowPanel:function() {
			var masterPlan = $('#masterplanCombo').combobox('getValue');
			$(this).combobox('reload', 'src/testjob/get_testjob.php?plan=' + masterPlan);
		}
	});
	
	$("#tcDatagrid").datagrid({
		fit:true,
		rownumbers:true,
		fitColumns:false,
		singleSelect:true,
		border:true,
		pagination:true,
		pageSize:50,
		pageList:[10, 20, 30, 40, 50],
		columns:[[
          //{field:'ck', checkbox:true},
          /*{field:'run', title:'run?', 
        	  editor:{type:'checkbox', options:{on:'<img src="themes/icons/checkmark_12.png">', off:''}}, width:30},*/
          {field:'run', title:'run?', width:35},
          {field:'set', title:'set', width:30},
          {field:'executionMethod', title:'Type', width:100},
          {field:'testCaseName', title:'testCaseName', width:300},
          {field:'caseDescription', title:'caseDescription', width:500},
          {field:'scriptPath', title:'scriptPath', width:500},
          {field:'gitPath', title:'gitPath', width:500}
		]],
		onLoadSuccess:function() {
			// check the row
			//$('#tcDatagrid').datagrid('selectRow', curPosition);
		},
		onClickCell:function(rowIndex, field, value) {
			var row = $(this).datagrid('getRows');
			
			if (field == 'run' && row[rowIndex].executionMethod == 'Automated') { 
				(value == "<img src='themes/icons/checkmark_12_lightgray.png'>") ? updateRun(rowIndex, true) : updateRun(rowIndex, false);
			}
		},
		onSelect:function(rowIndex, rowData) {
			if (rowData.executionMethod == 'Automated') {
				(rowData.run == "<img src='themes/icons/checkmark_12_lightgray.png'>") ? updateRun(rowIndex, true) : updateRun(rowIndex, false);
			}
		}
	});
	
	$('#deviceDatagrid').datagrid({
		fit:true,
		rownumbers:false,
		fitColumns:false,
		singleSelect:true,
		border:false,
		onSelect:function(rowIndex, rowData) {
			checkDevice(rowData.name);
			getJobQueue(rowData.serial);
			clearInterval(logParseInterval);
		}
	});
	
	$('#mmRunBtn').menu({
		onClick:function(item) {
			switch (item.text) {
			case 'All':
				updateRun(undefined, true);
				break;
			case 'None':
				updateRun(undefined, false);
				break;
			}
		}
	});
	
	$('#dgSettings').datagrid({
		fitColumns:true,
		singleSelect:true,
		fit:true,
		border:false,
		toolbar:[{
			iconCls:'icon-add-darkgray',
			handler:function() {appendRow('#dgSettings');}
		},{
			iconCls:'icon-delete-darkgray',
			handler:function() {removeRow('#dgSettings');}
		},{
			iconCls:'icon-browse',
			handler:function() {browseFile('#dgSettings');}
		},{
			iconCls:'icon-check-darkgray',
			handler:function() {acceptEditRow('#dgSettings');}
		}],
		columns:[[
		    {field:'key',title:'Setting Key',width:100,editor:'text'},
		    {field:'value',title:'Setting Value',width:100,editor:'text'}
		]],
		onClickRow:function(rowIndex, rowData) {
			onClickRow(this, rowIndex);
		}
	});
	
	$('#logDg').datagrid({
		fit:true,
		fitColumns:true,
		border:false,
		singleSelect:true,
	    columns:[[
	        {field:'log',title:'Log Message',width:100}
        ]]
	});
	
	$('#jobQueueDg').datagrid({
		fit:true,
		fitColumns:true,
		border:false,
		singleSelect:true,
		onRowContextMenu:function(e, rowIndex, rowData) {
			e.preventDefault();
			
			$(this).datagrid('selectRow', rowIndex);
			
			// disable delete menu item if the job's status is running
			(rowData.status == 'running') ? 
			$('#mm').menu('disableItem', $('#m-delete')[0]) : $('#mm').menu('enableItem', $('#m-delete')[0]);
			
			$('#mm').menu({
				onClick:function(item) {
					switch (item.text) {
						case 'Delete':
							var name;
							
							// get the currently selected client
							$('#clients img').each(function() {
								if ($(this).attr('src') == 'themes/icons/access_point_60.png') {
									name = $(this).attr('id');
								}
							});
							
							var device = $('#deviceDatagrid').datagrid('getSelected');
							 
							$.post('src/testjob/delete_testjob', 
								{id:rowData.job_id, testMachine:name, testJob:rowData.test_job, device:device.serial}, 
								function(resp) {
									if (resp != 1) {
										alert(resp);
									} else {
										// refresh the test job queue
										getJobQueue(device.serial);
									}
								}
							);
							break;
						case 'View Status':
							var path = rowData.log_path;
							
							viewStatus(path); // run immediately once
							logParseInterval = setInterval(function() {viewStatus(path);}, 10000);
							break;
						case 'View Detail':
							var path = rowData.log_path;
							
							if (path) {
								path = path.replace(".log", "/");
								path = path.replace("status", "workspace");
							
								if (rowData.status == "ERROR") {
									path = path + "errorTraceback.txt";
								} else {
									path = path + "consoleLog.txt";
								}
								
								viewStatus(path); // run immediately once
								logParseInterval = setInterval(function() {viewStatus(path);}, 10000);
							} else {
								alert('Test has not been started yet.');
							}
							break;
					}
				}
			}).menu('show', {
				left:e.pageX,
				top:e.pageY
			});
		},
		onSelect:function(rowIndex, rowData) {
			$('#testjobName').text(rowData.test_job);
			clearInterval(logParseInterval);
		}
	});
	
	// customized validation rule
	$.extend($.fn.validatebox.defaults.rules, {
		validServer: {
			validator: function(value) {
				return value.match("^http://jenkins-main.am.mot.com/") || value.match("^http://jenkins-upgrade.am.mot.com/");
			},
			message: 'URL should starts with http://jenkins-main.am.mot.com/ or http://jenkins-upgrade.am.mot.com/'
		}
	});
	
	$('#endOnCC').combobox({
		onSelect:function(record) {
			var value = $(this).combobox('getValue');
			
			switch(value) {
				case 'After':
					$('#divEndDate').hide();
					$('#divOccurrence').show();
					break;
				case 'On':
					$('#divEndDate').show();
					$('#divOccurrence').hide();
					break;
				case 'Never':
					$('#divEndDate').hide();
					$('#divOccurrence').hide();
					break;
			}
			
			repeatSummary();
		}
	});
	
	$('#repeatCC').combobox({
		onSelect:function(record) {
			if (record.text == 'Weekly') {
				var currentTime = new Date();
				var day = currentTime.getDay();
				
				checkWeekday(weekday[day]);
				$('#repeatOn').show();
			} else {
				$('#repeatOn').hide();
			}
			repeatSummary();
		}
	});
	
	$('#repeatEveryCC').combobox({
		onSelect:function() {
			repeatSummary();
		}
	});
	
	$('#occurrenceSS').numberspinner({
		onChange:function() {
			repeatSummary();
		}
	});
	
	$('#startDate').datetimebox({
		onChange:function(date) {
			$('#endDate').datetimebox('setValue', date);
		}
	});
	
	$('#endDate').datetimebox({
		onChange:function(date) {
			repeatSummary();
		}
	});
	
	$('#repeatOn :checkbox').change(function() {
		repeatSummary();
	});
	
	$('#profile').combobox({
		onSelect:function() {
			if ($('#profile').combobox('getValue') == "") {
				$('#profile').combobox('setText', '');
			}
		}
	});
	
	$('#profile').combobox('setText', '');
	
	$('#clients').click(function(event) {
		if (event.target.nodeName == 'IMG') {
			// clear previously set interval
			if (curInterval != undefined) {
				clearInterval(curInterval);
			}
			
			// get the id of child element
			curClientId = event.target.id;
			//var name = event.target.nodeName;
			
			// reset the image of all clients
			$('#clients img').each(function() {
				$(this).attr('src', 'themes/icons/access_point_60_light_gray.png');
			});
			
			// get device and show clock
			setTimeout(function() {getDevices(curClientId);}, 2000);
			
			// change the image src
			$(event.target).attr('src', 'themes/icons/access_point_60.png');
		}
		
		event.preventDefault();
	});
	
	$("#dgTestResult").datagrid({
		fit:true,
		rownumbers:true,
		fitColumns:true,
		singleSelect:true,
		border:true,
		pagination:false,
		pageSize:50,
		pageList:[10, 20, 30, 40, 50],
		onSelect:function(index, rowData){
			showTestResultDetail(rowData);
		}
	});
	
	/**
	 * group combo box on 3rd tab: Monitor Test Result
	 */
	/*
	$('#groupCombo1').combobox({
		valueField:'id', 
		textField:'text', 
		url:'src/testmonitor/get_group.php?user_name='+username,
        onSelect : function() {
            var group = $('#groupCombo1').combobox('getValue');
            
            if ( group == "--Select group--" || group == "default" || group ==null){
            	
            } else {
            	var win = $.messager.progress({
                           	text:'Loading master plan...',
                           	interval:'900'
                   	});
                
            	$.post('src/testjob/get_master_plan.php',{group:group},function(result){
            		if (result.msg == "Empty info from Test Central") {
            			alert(result.msg);
            		} else {
            			$('#masterplanCombo1').combobox('loadData', result);
            		}
            		$.messager.progress('close');
            	},'json');
            }
		},
		onLoadSuccess:function() {
			$.ajaxSetup({cache: false});
			$.get('src/testjob/read_tp_testjob_list.php', {user_name:username}, function(result) {
				var jsonArr = JSON.parse(result);
				group = jsonArr["group"].replace('(', '').replace(')', '');
				plan = jsonArr["plan"];
				
				$('#groupCombo').combobox('select', group);
			});
			//$('#groupCombo').combobox('select', globalGroup);
		},
		onCheck:function() {
			
		}
	});*/
	
	/**
	 * master plan combo box on 3rd tab: Monitor Test Result
	 */
	$('#masterplanCombo1').combobox({
		valueField:'id',
		textField:'text',
		url:'src/testmonitor/get_testreport_xml.php',
		onSelect:function() {
			$('#cgTestResult').combogrid('setValue', '');
			
			$.ajaxSetup({cache:false});
			$.post('src/testmonitor/get_testreport_xml.php', {masterplan:$(this).combobox('getText')}, function(result) {
				try {
					var arrResult = JSON.parse(result);
					$('#cgTestResult').combogrid({
						data:arrResult
					});
				} catch (e) {
					alert('Error detect in parsing JSON ' + e);
				}
			});
		}
	});
	
	$('#cgTestResult').combogrid({
		idField:'id',
		textField:'id',
		fitColumns:true,
		panelWidth:750,
		columns:[[
		          {field:'id', title:'id', width:50, hidden:true},
		          {field:'device', title:'device', width:100},
		          {field:'time', title:'time', width:100},
		          {field:'scope', title:'scope', width:200},
		          {field:'loop', title:'loop number', width:50}
		]],
		filter:function(q, row) {
			var opts = $(this).combogrid('options');
			
			// select row which contains query text (q)
			return row[opts.textField].indexOf(q) >= 0;
		},
		onSelect:function(rowIndex, rowData) {
			getTestreport(rowData.id);
		}
	});
	
	/*
	$('#testreportCombo').combobox({
		valueField:'id',
		textField:'text',
		formatter: formatItem,
		onSelect:function() {
			getTestreport($(this).combobox('getValue'));
		}
	});*/
	
	/**
	 * validatebox validate rule function
	 * Jung Soo Kim
	 * Feb 27, 2014
	 */
	$.extend($.fn.validatebox.defaults.rules, {
		maxLength: {
			validator: function(value, param) {
				if (value.length <= param[0]) {
					return true;
				} else {
					$('#scope').val(value.substr(0, param[0]));
				}
			},
			message: 'You can enter {50} characters at max.'
		}
	});
	
	$('#dlgJobSchedule').dialog({
		title:'Schedule',
		iconCls:'icon-calendar',
		closed:true,
		modal:true,
		buttons:'#bb',
		onOpen:function() {
			setStartTime();
			$('#ckRepeat').prop('checked', false);
			showRepeat($('#ckRepeat'));
		}
	});
	
	$('#dlgSettings').dialog({
		iconCls:'icon-folder',
		closed:true,
		modal:true,
		buttons:'#bbSettings',
		onOpen:function() {
			// retrieve key value
			var keyValue = $('#dgSettings').datagrid('getRows')[editIndex]['value'];
			var lastIndex = keyValue.lastIndexOf('/');
			
			// if no path is specified, open the top directory of the repo
			if (lastIndex > 0) {
				var keyPath = keyValue.substr(0, lastIndex);
				readDir(keyPath)
			} else {
				readDir();
			}
		}	
	});
	
	$('#dgFileBrowser').datagrid({
		fitColumns:true,
		singleSelect:true,
		fit:true,
		border:false,
		columns:[[
		    {field:'name',title:'Name',width:100},
		    {field:'size',title:'Size',width:100},
		    {field:'type',title:'Type',width:100}
		]],
		onClickRow:function(rowIndex, rowData) {
			if (rowData['type'] == 'folder') {
				var curPath = $('#dlgSettings').dialog('options').title;
				
				if (rowData['name'] == "..") {
					var lastIndex = curPath.lastIndexOf('/');
					var parent = curPath.substr(0, lastIndex);
					readDir(parent);
				} else if (rowData['name'] == ".") {
					// read repo directory
					readDir();
				} else {
					readDir(curPath + '/' + rowData['name']);
				}
			}
		}
	});
	
	function readDir(curDir) {
		$.post('src/testjob/readDir.php', {machine:curClientId, path:curDir}, function(result) {
			try {
				var result = JSON.parse(result);
				
				// load file list
				$('#dgFileBrowser').datagrid({
					data:result.filelist
				});
				
				// update the current path
				$('#dlgSettings').dialog('setTitle', result.pwd);
				$('#dlgSettings').dialog('options').title = result.pwd;
			} catch (e) {
				alert(e + '\n' + result);
			}
		});
	}
	
	$('#passCriteriaSlider').slider({
		mode:'h',
		value:50,
		onChange:function(value) {
			var msg;
			
			if (value == 0) {
				msg = "Test result will ALWAYS be recorded as \'pass\'.";
			} else if (value == 100) {
				msg = "Test result will be recorded as \'pass\' ONLY 100% passed.";
			} else {
				msg = "Test result will be recorded as \'pass\' if pass rate reaches " + value +"%.";
			}
			
			// shade the green color by percentage
			var R = Math.round(2.00 * value).toString(16);
			var B = Math.round(2.00 * value).toString(16);
			
			$('#sliderTip').text(msg);
			$('#sliderTip').css("background-color", "#" + R + "ff" + B);
		}
	});
	
	$('#testjobLoops').numberspinner({
		onChange:function(newValue) {
			onChangeHandler(newValue);
		}
	});
	
	/**********************
	 * Initialization
	 ********************/
	$('#groupCombo1').combobox('select', 0);
	$('#comboTestjobRun').combobox('select', '');
	fitWindowSize();
});

/*
 * combobox overriden filter
 * combobox filters item as entering a text
 * Jung Soo Kim
 */
$.fn.combobox.defaults.filter = function(q, row) {
	var opts = $(this).combobox('options');
	return row[opts.textField].indexOf(q) >= 0;
};

function onChangeHandler(value) {
	if (value > 1 && $('#uploadResult').prop('checked')) {
		$('#passCriteriaSlider').slider('setValue', 50);
		$('#passRateSlider').show();
	} else {
		$('#passCriteriaSlider').slider('setValue', 0);
		$('#passRateSlider').hide();
	}
}

function loadTestCasesFromMasterPlan() {
	var group = $('#groupCombo').combobox('getValue');
	var masterplan = $('#masterplanCombo').combobox('getValue');
	
	$.messager.progress({
       	text:'Reading data from TestCentral......',
       	interval:'900'
	});
	
	$.ajaxSetup({cache: false});
	$.get('src/testjob/readTestJobJson.php?username=' + username, {group:group, planname:masterplan}, function(result) {
		$.messager.progress('close');
		
		// since it copies to temp file, it is required to reload
		$('#tcDatagrid').datagrid('reload');
	});
}

function readTestJob(testjob) {
	var group = $('#groupCombo').combobox('getValue');
	var masterplan = $('#masterplanCombo').combobox('getValue');
	
	$.messager.progress({
       	text:'Reading data from TestCentral......',
       	interval:'900'
	});
	
	$.ajaxSetup({cache: false});
	$.get('src/testjob/readTestJobJson.php?username=' + username, {group:group, planname:masterplan, filename:testjob}, function(result) {
		$.messager.progress('close');
		
		// since it copies to temp file, it is required to reload
		$('#tcDatagrid').datagrid('reload');
		
		// load configurations
		try {
			var arrResult = JSON.parse(result);
			readTestJobConfig(arrResult);
		} catch (e) {
			alert(e);
		}
	});
}

function readTestJobConfig(array) {
	globalGroup = array.group;
	globalPlan = array.testplan;
	
	if (typeof array !== 'undefined') {
		$('#scope').val(array.scope);
		$('#testjobProdHW').val(array.productHW);
		$('#testjobBuild').val(array.build);
		$('#testjobUrl').val(array.url);
		$('#testjobRetrySpin').numberspinner('setValue', array.retry);
		$('#testjobLoops').numberspinner('setValue', array.loops);
		$('#verifyRAM').prop('checked', array.verifyRAM);
		$('#startDate').datetimebox('setValue', array.start);
		$('#repeatCC').combobox('select', array.repeats);
		$('#profile').combobox('select', array.profile);
		$('#passCriteriaSlider').slider('setValue', Math.round(array.passCriteria * 100));
		$('#createCyclePlan').prop('checked', array.createCyclePlan);
		$('#uploadResult').prop('checked', array.uploadResult);
		$('#uploadResult').trigger('change');  // trigger onChange event
		
		// clear hidden values
		$('#testjobUrlHidden').val("");
		$('#testjobBuildHidden').val("");
		
		if (array.build.length > 0 && array.url.length > 0) {
			$('#ckDownload').prop('checked', true);
			$('#testjobBuild').prop('disabled', false);
			$('#testjobUrl').prop('disabled', false);
		} else {
			$('#ckDownload').prop('checked', false);
			$('#testjobBuild').prop('disabled', true);
			$('#testjobUrl').prop('disabled', true);
		}
		
		if (array.repeats == "Weekly") {
			checkWeekday(array.weeklyOn);
			$('#repeatOn').show();
		}
		
		$('#repeatEveryCC').combobox('select', array.every);
		$('#endOnCC').combobox('select', array.end);
		
		if (array.end == "On") {
			$('#endDate').datetimebox('setValue', array.on);
		} else if (array.end == "After") {
			$('#occurrenceSS').numberspinner('setValue', array.after);
		}
		
		// check whether repeat or not
		if ($('#repeatCC').combobox('getText') == 'Daily' &&
			$('#repeatEveryCC').combobox('getValue') == '1' &&
			$('#endOnCC').combobox('getValue') == 'After' &&
			$('#occurrenceSS').numberspinner('getValue') == 1) {
			$('#ckRepeat').prop('checked', false);
			$('#repeat').hide();
		} else {
			$('#ckRepeat').prop('checked', true);
			$('#repeat').show();
		}
	}
}

function clearTestJobConfig() {
	$('#scope').val('');
	$('#testjobProdHW').val('');
	$('#testjobBuild').val('');
	$('#testjobUrl').val('');
	$('#testjobRetrySpin').numberspinner('setValue', 0);
	$('#testjobLoops').numberspinner('setValue', 1);
	$('#verifyRAM').prop('checked', false);
	$('#profile').combobox('select', '');
	$('#comboTestjob').combobox('select', 0);
	$('#ckDownload').prop('checked', false);
	$('#testjobBuild').prop('disabled', true);
	$('#testjobUrl').prop('disabled', true);
	$('#testjobBuild').val("");
	$('#testjobUrl').val("");
	$('#testjobUrlHidden').val("");
	$('#testjobBuildHidden').val("");
	$('#uploadResult').prop('checked', false);
	$('#uploadResult').trigger('change');  // trigger onChange event
}

function checkWeekday(weekdays) {
	var arrWeekday = weekdays.split(",");
	
	for (var index in arrWeekday) {
		switch (arrWeekday[index].trim()) {
			case weekday[0]: // SUN
				$('#ckSun').prop("checked", true);
				break;
			case weekday[1]:
				$('#ckMon').prop("checked", true);
				break;
			case weekday[2]:
				$('#ckTue').prop("checked", true);
				break;
			case weekday[3]:
				$('#ckWed').prop("checked", true);
				break;
			case weekday[4]:
				$('#ckThur').prop("checked", true);
				break;
			case weekday[5]:
				$('#ckFri').prop("checked", true);
				break;
			case weekday[6]:
				$('#ckSat').prop("checked", true);
				break;
		}
	}
}

function getDate() {
	var currentTime = new Date();
	var day = currentTime.getDay();
	
	switch (day) {
		case 0: // SUN
			return "Sunday";
			break;
		case 1:
			return "Monday";
			break;
		case 2:
			return "Tuesday";
			break;
		case 3:
			return "Wednesday";
			break;
		case 4:
			return "Thursday";
			break;
		case 5:
			return "Friday";
			break;
		case 6:
			return "Saturday";
			break;
	}
}

function setStartTime() {
	var clockText;
	
	// get the client machine name of highlighted
	$('#clients img').each(function() {
		if ($(this).attr('src') == 'themes/icons/access_point_60.png') {
			client = $(this).attr('id');
			clockText = $('#' + client.replace(/ /gi, '_') + ' #clock').text().replace(/(\r\n|\n|\r)/gm,""); 
		}
	});
	
	// get date object
	arrClockText = clockText.split(' ');
	arrDate = arrClockText[0].split('-');
	arrTime = arrClockText[1].split(':');
	
	var d = new Date(arrDate[2], arrDate[0], arrDate[1], arrTime[0], arrTime[1], arrTime[2]);
	
	// add 1 minute if more than 20 seconds left
	// otherwise, add 2 minutes
	if (d.getSeconds() > 40) {
		// add 2 minutes
		d.setMinutes(d.getMinutes() + 2);
	} else {
		// add 1 minute
		d.setMinutes(d.getMinutes() + 1);
	}
	
	var startDateTime = d.getMonth() + '/' + d.getDate() + '/' + d.getFullYear() + ' ' + 
						d.getHours() + ':' + d.getMinutes() + ':00';
	
	$('#startDate').datetimebox('setValue', startDateTime);
}

function repeatSummary() {
	var repeat = $('#repeatCC').combobox('getText');
	var every = $('#repeatEveryCC').combobox('getText');
	var end = $('#endOnCC').combobox('getText');
	var summary;
	var weekdays;
	
	switch (repeat) {
		case 'Min':
			summary = 'Every ' + every + ' minutes';
			break;
		case 'Hour':
			summary = 'Every ' + every + ' hours';
			break;
		case 'Daily':
			if (every == '1') {
				summary = repeat;
			} else {
				summary = 'Every ' + every + ' days';
			}
			break;
		case 'Weekly':
			if (every == '1') {
				summary = repeat;
			} else {
				summary = 'Every ' + every + ' weeks';
			}
			
			weekdays = getWeekday();
			
			if (weekdays.length == 0) {
				weekdays = getDate();
			}
			
			summary = summary + " on " + weekdays;
			break;
		case 'Monthly':
			if (every == '1') {
				summary = repeat;
			} else {
				summary = 'Every ' + every + ' months';
			}
			break;
	}
	
	if (end == 'After') {
		summary = summary + ', ' + $('#occurrenceSS').numberspinner('getValue') + ' times';
	} else if (end == 'On') {
		summary = summary + ', until ' + $('#endDate').datetimebox('getValue');
	}	

	$('#repeatSummary').text(summary);
}

function getWeekday() {
	var arrDays = new Array();
	
	if ($('#ckSun').prop("checked")) {
		arrDays.push(weekday[0]);
	}
	
	if ($('#ckMon').prop("checked")) {
		arrDays.push(weekday[1]);
	} 
	
	if ($('#ckTue').prop("checked")) {
		arrDays.push(weekday[2]);
	}
	
	if ($('#ckWed').prop("checked")) {
		arrDays.push(weekday[3]);
	}
	
	if ($('#ckThur').prop("checked")) {
		arrDays.push(weekday[4]);
	}
	
	if ($('#ckFri').prop("checked")) {
		arrDays.push(weekday[5]);
	}
	
	if ($('#ckSat').prop("checked")) {
		arrDays.push(weekday[6]);
	}
	
	return arrDays.join(", ");
}

function controlMoveBtn() {
	var checked = $('#tcDatagrid').datagrid('getChecked');
	
	if (checked.length == 1) {
		$('#btnDownEnd').linkbutton('enable');
		$('#btnDown').linkbutton('enable');
		$('#btnUp').linkbutton('enable');
		$('#btnUpEnd').linkbutton('enable');
		
		//$('#selGroup').combobox('select', checked[0]['set']);

		if (checked[0]['run'] == '<img src="themes/icons/checkmark_12.png">') {
			$('#ckRun').prop('checked', true);
		} else {
			$('#ckRun').prop('checked', false);
		}
	} else {
		$('#btnDownEnd').linkbutton('disable');
		$('#btnDown').linkbutton('disable');
		$('#btnUp').linkbutton('disable');
		$('#btnUpEnd').linkbutton('disable');
		
		//$('#selGroup').combobox('select', '');
		$('#ckRun').prop('checked', false);
	}
}

function moveRow(direction) {
	var pageSize = $('#tcDatagrid').datagrid('options').pageSize;
	var pageNum = $('#tcDatagrid').datagrid('options').pageNumber;
	var index = (pageNum - 1) * pageSize + curPosition;
	
	// update temp JSON
	$.get('src/testjob/update_testjob_pos.php', {user_name:username, index:index, direction:direction}, 
		function(newIndex) {
			// get page number and new index row to indicate the check
			curPageNum = parseInt(newIndex / pageSize) + 1;
			curPosition = newIndex % pageSize;
			
			//alert('New Index=' + newIndex + '\n' + 'curPageNum=' + curPageNum + '\n' + 'curPosition=' + curPosition);
			
			// go to the current page number
			$('#tcDatagrid').datagrid({
				pageNumber:curPageNum
			});
			
			$('#tcDatagrid').datagrid('reload');
		}
	);
	
	/*
	var pageSize = $('#tcDatagrid').datagrid('options').pageSize;
	var pageNum = $('#tcDatagrid').datagrid('options').pageNumber;
	var checked = $('#tcDatagrid').datagrid('getSelected');
	var data = $('#tcDatagrid').datagrid('getData');
	var rows = data["rows"];
	var total = data["total"];
	
	curCheckedRow = $('#tcDatagrid').datagrid('getRowIndex', checked[0]);

	var indexCursor = (pageNum - 1) * pageSize + curCheckedRow;
	alert(indexCursor);

	switch (direction) {
		case 'moveLast':
			$.ajaxSetup({cache: false});
			$.get('src/testjob/update_testjob_pos.php', {user_name:username, index:indexCursor, 
				direction:direction}, function(index) {
					
				// get page number and new index row to indicate the check
				var pageSize = $('#tcDatagrid').datagrid('options').pageSize;
				var pageNum = $('#tcDatagrid').datagrid('options').pageNumber;
				curPageNum = parseInt(index / pageSize) + 1;
				curCheckedRow = index % pageSize;
				
				// go to the current page number
				$('#tcDatagrid').datagrid({
					pageNumber:curPageNum
				});
			});
			break;
		case 'moveNext':
			if (indexCursor < total - 1) {
				rows.splice(curCheckedRow, 1);
				curCheckedRow = curCheckedRow + 1;
				rows.splice(curCheckedRow, 0, checked[0]);
				data["rows"] = rows;
				
				// update temp JSON
				$.get('src/testjob/update_testjob_pos.php', {user_name:username, index:indexCursor, 
					direction:direction}, function(index) {
						
					// if checked row index is within the range of pageSize
					// update on client side
					// otherwise, reload from the server
					var pageSize = $('#tcDatagrid').datagrid('options').pageSize;
					var pageNum = $('#tcDatagrid').datagrid('options').pageNumber;
					var indexMin = (pageNum - 1) * pageSize;
					var indexMax = (pageNum - 1) * pageSize + pageSize - 1;
					
					if (index >= indexMin && index <= indexMax) {
						$('#tcDatagrid').datagrid('loadData', data);
						$('#tcDatagrid').datagrid('checkRow', curCheckedRow);
					} else {
						// get page number and new index row to indicate the check
						curPageNum = parseInt(index / pageSize) + 1;
						curCheckedRow = index % pageSize;
						
						// go to the current page number
						$('#tcDatagrid').datagrid({
							pageNumber:curPageNum
						});
					}
				});
			}
			break;
		case 'movePrev':
			if (indexCursor > 0) {
				rows.splice(curCheckedRow, 1);
				curCheckedRow = curCheckedRow - 1;
				rows.splice(curCheckedRow, 0, checked[0]);
				data["rows"] = rows;
				
				// update temp JSON
				$.get('src/testjob/update_testjob_pos.php', {user_name:username, index:indexCursor, 
					direction:direction}, function(index) {
											
					// if checked row index is within the range of pageSize
					// update on client side
					// otherwise, reload from the server
					var pageSize = $('#tcDatagrid').datagrid('options').pageSize;
					var pageNum = $('#tcDatagrid').datagrid('options').pageNumber;
					var indexMin = (pageNum - 1) * pageSize;
					var indexMax = (pageNum - 1) * pageSize + pageSize - 1;
					
					if (index >= indexMin && index <= indexMax) {
						$('#tcDatagrid').datagrid('loadData', data);
						$('#tcDatagrid').datagrid('checkRow', curCheckedRow);
					} else {
						// get page number and new index row to indicate the check
						curPageNum = parseInt(index / pageSize) + 1;
						curCheckedRow = index % pageSize;
						
						// go to the current page number
						$('#tcDatagrid').datagrid({
							pageNumber:curPageNum
						});
					}
				});
			}
			break;
		case 'moveFirst':
			$.ajaxSetup({cache: false});
			$.get('src/testjob/update_testjob_pos.php', {user_name:username, index:indexCursor, 
				direction:direction}, function(index) {
					
				// get page number and new index row to indicate the check
				var pageSize = $('#tcDatagrid').datagrid('options').pageSize;
				var pageNum = $('#tcDatagrid').datagrid('options').pageNumber;
				curPageNum = parseInt(index / pageSize) + 1;
				curCheckedRow = index % pageSize;
				
				// go to the current page number
				$('#tcDatagrid').datagrid({
					pageNumber:curPageNum
				});
			});
			break;
	}*/
}

function saveTestJob() {
	var arrConfig = {};
	
	arrConfig["scope"] = $('#scope').val();
	arrConfig["productHW"] = $('#testjobProdHW').val();
	arrConfig["build"] = $('#testjobBuild').val();
	arrConfig["url"] = $('#testjobUrl').val();
	arrConfig["retry"] = $('#testjobRetrySpin').val();
	arrConfig["loops"] = $('#testjobLoops').val();
	arrConfig["verifyRAM"] = $('#verifyRAM').prop('checked');
	arrConfig["profile"] = $('#profile').combobox('getValue');
	arrConfig["passCriteria"] = ($('#passCriteriaSlider').slider('getValue') / 100);
	arrConfig["uploadResult"] = $('#uploadResult').prop('checked');
	arrConfig["createCyclePlan"] = $('#createCyclePlan').prop('checked');
	
	$.ajaxSetup({cache: false});
	$.post('src/testjob/save_testjob.php', {user_name:username, config:JSON.stringify(arrConfig)}, function(result) {
		clearTestJobConfig();
		$('#tcDatagrid').datagrid('reload');
		alert(result);
	});
}

function saveTestJobSchedule(when) {
	var arrConfig = {};
	var testjobFile = $('#comboTestjobRun').combobox('getText');
	
	// get schedule information
	arrConfig["start"] = $('#startDate').datetimebox('getValue');
	
	if (when == 'now') {
		arrConfig["repeats"] = "Daily";
		arrConfig["every"] = "1";
		arrConfig["end"] = "After";
		arrConfig["after"] = "1";
	} else {
		arrConfig["repeats"] = $('#repeatCC').combobox('getValue');
		arrConfig["every"] = $('#repeatEveryCC').combobox('getValue');
		
		if ($('#repeatCC').combobox('getText') == 'Weekly') {
			arrConfig["weeklyOn"] = getWeekday();
		}
		
		arrConfig["end"] = $('#endOnCC').combobox('getValue');
		
		if ($('#endOnCC').combobox('getValue') == 'After') {
			arrConfig["after"] = $('#occurrenceSS').val();
		} else if ($('#endOnCC').combobox('getValue') == 'On') {
			arrConfig["on"] = $('#endDate').datetimebox('getValue');
		}
	}  
	
	$.ajaxSetup({cache: false});
	$.post('src/testjob/saveTestJobSchedule.php', {file:testjobFile, config:JSON.stringify(arrConfig)}, function(result) {
	});
}

function updateRun(index, setRun) {
	var pageSize = $('#tcDatagrid').datagrid('options').pageSize;
	var pageNum = $('#tcDatagrid').datagrid('options').pageNumber;
	var indexUpd = (pageNum - 1) * pageSize + index;
	var rowData = {};
	
	if (setRun) {
		rowData['run'] = "<img src='themes/icons/checkmark_12.png'>";
		rowData['set'] = $('#selGroup').combobox('getValue');
	} else {
		rowData['run'] = "<img src='themes/icons/checkmark_12_lightgray.png'>";
		rowData['set'] = '';
	}
	
	$.ajaxSetup({cache: false});
	$.post('src/testjob/updateRun.php',
	{userName:username, index:indexUpd, changes:JSON.stringify(rowData)}, function(result) {
		$('#tcDatagrid').datagrid('reload');
	});
}

function saveTestJobList() {
	var indexes = new Array();
	var pageSize = $('#tcDatagrid').datagrid('options').pageSize;
	var pageNum = $('#tcDatagrid').datagrid('options').pageNumber;
	var checkedRows = $('#tcDatagrid').datagrid('getChecked');
		
	for (var i = 0; i < checkedRows.length; i++) {
		// remember index row
		var indexRow = $('#tcDatagrid').datagrid('getRowIndex', checkedRows[i]);
		indexes.push((pageNum - 1) * pageSize + indexRow);
				
		// update run
		if ($('#ckRun').is(':checked')) {
			checkedRows[i]['run'] = '<img src="themes/icons/checkmark_12.png">';
			checkedRows[i]['set'] = $('#selGroup').combobox('getValue');
		} else {
			checkedRows[i]['run'] = '';
			checkedRows[i]['set'] = '';
		}
	}
	
	$.ajaxSetup({cache: false});
	$.post('src/testjob/update_testjob_list.php',
			{user_name:username, index:JSON.stringify(indexes), changes:JSON.stringify(checkedRows)}, function(result) {
		$('#tcDatagrid').datagrid('reload');
	});
}

function fitWindowSize() {
    // fit mainContainer to body size
    document.getElementById("mainContainer").style.height = $('body').height() - 41 + 'px';
    
    //alert($('body').height() + ' ' + document.getElementById('mainContainer').style.height);
    
    // refresh mainTabs
    $('#mainTabs').tabs('resize');
    
    // select the tab again to complete the resizing the tab
    var tab = $('#mainTabs').tabs('getSelected');
    $('#mainTabs').tabs('select', tab.panel('options').title);
}

function getDevices(machine) {

	$.messager.progress({
		text:'Detecting devices...',
		interval:'900'
	});
	
	$.post('src/testjob/get_devices.php', {name:machine, userid:username}, function(result) {
		$.messager.progress('close');
		
		var fields = [];
		
		// if returned result string is valid JSON, it will throw exception
		// otherwise, it must be error message, it should be displayed
		try {
			var data = JSON.parse(result);
		} catch(e) {
			alert(result);
		}
		
		var devices = data.devices;
		var settings = data.settings;

		// initialization
		$('#deviceDatagrid').datagrid({
			data:[]
		});
		
		$('#dgSettings').datagrid({
			data:[]
		});
		
		for (var key in devices) {
			if (devices.hasOwnProperty(key)) {
				for (var prop in devices[key]) {
					if (fields.indexOf(prop) == -1) {
						fields.push(prop);
					}
				}
			}
		}
		
		var columns = [];
		for (var index in fields) {
			switch(fields[index]) {
				case 'serial':
					colWidth = 130;
					break;
				case 'image':
					colWidth = 40;
					break;
				default:
					colWidth = 100;
					break;
			}
			
			columns.push({field:fields[index], title:fields[index], width:colWidth});
		}

		$('#deviceDatagrid').datagrid({
			columns:[columns],
			data:devices
		});
		
		$('#dgSettings').datagrid({
			data:settings
		});
		
		// remove the clock display of each test machine
		$('#clients img').each(function() {
			// clear clock display
			var id = $(this).attr('id').replace(/ /gi, '_');
			$('#' + id + ' #clock').html('&nbsp;');
		});
		
		// start display clock of the test machine
		curInterval = setInterval(function() {showClock(machine);}, 1000);
		
		// make sure to clear the clock display of each test machine
		setTimeout(function() {
			$('#clients img').each(function() {
				var id = $(this).attr('id')

				if (id != machine) {
					// clear clock display
					$('#' + id.replace(/ /gi, '_') + ' #clock').html('&nbsp;');
				}
			});
		}, 2000);
	});
}

function showClock(machine) {
	var machine_id = machine.replace(/ /gi, '_');
	
	$.post('src/testjob/get_clients_system_time.php', {name:machine}, function(date) {
		$('#' + machine_id + ' #clock').html(date);
	});
}

function runTestJob(when) {

	var testjob = $('#comboTestjobRun').combobox('getText');
	var device = $('#deviceDatagrid').datagrid('getSelected');
	var client;
	
	// get the client machine name of highlighted
	$('#clients img').each(function() {
		if ($(this).attr('src') == 'themes/icons/access_point_60.png') {
			client = $(this).attr('id'); 
		}
	});
	
	if (!client) {
		alert('Please select client machine!');
	} else if (!device) {
		alert('Please select device!');
	} else if (!testjob) {
		alert('Please select test job!');
	} else {
		saveTestJobSchedule(when);
		
		$.post('src/testjob/run_testjob.php', {file:testjob, serial:device["serial"], sentby:username, client:client}, function(result) {
			alert(result);
		});
	}
}

function getJobQueue(serial) {

	var machine;
	
	$.messager.progress({
		text:'Reading jobs...',
		interval:'900'
	});
	
	// get the repo path of the currently selected client
	/*$('#clients img').each(function() {
		if ($(this).attr('src') == 'themes/icons/access_point_60.png') {
			name = $(this).attr('id');
		}
	});*/
	
	$.post('src/testjob/get_job_queue.php', {device:serial, machine:curClientId}, function(result) {
		$.messager.progress('close');
		
		var fields = [];
		var data = JSON.parse(result);

		// initialization
		$('#jobQueueDg').datagrid({
			data:[]
		});
		
		for (var key in data) {
			if (data.hasOwnProperty(key)) {
				for (var prop in data[key]) {
					if (fields.indexOf(prop) == -1 && prop != "job_id") {
						fields.push(prop);
					}
				}
			}
		}
		
		var columns = [];
		for (var index in fields) {
			
			switch(fields[index]) {
				case 'test_job':
					colWidth = 200;
					break;
				case 'status':
					colWidth = 50;
					break;
				default:
					colWidth = 130;
					break;
			}
			
			// hide log_path field
			if (fields[index] == "log_path") {
				columns.push({field:fields[index], title:fields[index], width:colWidth, hidden:true});
			} else {
				columns.push({field:fields[index], title:fields[index], width:colWidth});
			}
		}

		$('#jobQueueDg').datagrid({
			columns:[columns],
			data:data
		});
	});
}

function viewStatus(path) {
	
	// get the repo path of the currently selected client
	/*$('#clients img').each(function() {
		if ($(this).attr('src') == 'themes/icons/access_point_60.png') {
			name = $(this).attr('id');
		}
	});*/

	/*$.messager.progress({
		text:'Reading the log of test job...',
		interval:'900'
	});*/

	$.post('src/testjob/read_job_log.php', {logpath:path, machine:curClientId}, function(result) {
		//$.messager.progress('close');
		
		try {
			var data = JSON.parse(result);
			
			$('#logDg').datagrid({
				data:data
			});
			
			$('#logDg').datagrid('selectRow', data.length - 1);
		} catch (e) {
			alert(result);
		}
	});
}

function formatItem(row) {
	var s = '<img src="themes/icons/stop_16.png" style="vertical-align:middle;">&nbsp;' + row.device + '&nbsp;&nbsp;';
	
	if (row.scope.length > 0) {
		s +=  '<img src="themes/icons/find_in_file_16.png" style="vertical-align:text-bottom;">&nbsp;' + row.scope + '&nbsp;&nbsp;';
	}
	
	s += '<img src="themes/icons/clock_14.png" style="vertical-align:text-bottom;">&nbsp;' + row.date;
	
	return s;
}

function getTestreport(file) {
	$.ajaxSetup({cache:false});
	$.post('src/testmonitor/get_test_results.php', {reportfile:file}, function(result) {
		var fields = [];
		try {
			var data = JSON.parse(result);
		} catch(e) {
			alert(result);
		}
		
		// initialization
		$("#dgTestResult").datagrid({
			data:[]
		});
		
		for (var key in data) {
			if (data.hasOwnProperty(key)) {
				for (var prop in data[key]) {
					if (fields.indexOf(prop) == -1) {
						fields.push(prop);
					}
				}
			}
		}
		
		var columns = [];
		for (var index in fields) {
			listShow = ["name", "description", "time", "result"];
			
			if (jQuery.inArray(fields[index], listShow) != -1) {
				switch(fields[index]) {
				case 'description':
					colWidth = 400;
					break;
				case 'name':
					colWidth = 100;
					break;
				case 'time':
					colWidth = 50;
					break;
				default:
					colWidth = 30;
					break;
				}
				
				if (fields[index] == "result") {
					columns.push({field:fields[index], title:fields[index], width:colWidth, styler:function(value, row, index) {
							switch(value) {
								case 'ERROR':
									return 'background-color:yellow;color:red';
									break;
								case 'PASS':
									return 'background-color:green;color:white';
									break;
								case 'FAIL':
									return 'background-color:red;color:yellow';
									break;
							}
						}
					});
				} else {
					columns.push({field:fields[index], title:fields[index], width:colWidth});
				}
			} else {
				columns.push({field:fields[index], title:fields[index], width:5, hidden:true});
			}
		}
		
		$("#dgTestResult").datagrid({
			columns:[columns],
			data:data
		});
	});
}

function showTestResultDetail(rowData) {
	var strContent = "";
	
	strContent = "<div class=heading>" + rowData.name + "&nbsp;&nbsp;</div>";

	switch (rowData.result) {
		case 'PASS':
			strContent += "<div class=\"title pass\">" + rowData.result + "</div>";
			break;
		case 'FAIL':
			strContent += "<div class=\"title fail\">" + rowData.result + "</div>";
			break;
		case 'ERROR':
			strContent += "<div class=\"title error\">" + rowData.result + "</div>";
			break;
	}
	
	strContent += "<p class=text>" + rowData.description + "</p>" +
				  "<div class=title>Execution Time (sec)</div>" + "<p class=text>" + rowData.time + "</p>" +
				  "<div class=title>Address</div>" + "<p class=text>" + rowData.address + "</p>" +
				  "<div class=title>Trace Log</div>&nbsp;&nbsp;<a href=\"" + rowData.trace_log + "\" target=_blank>" +
				  "<img style=\"vertical-align:text-middle;\" src=\"themes/icons/search_16.png\"></a>&nbsp;&nbsp;" +
				  "<div class=title>Error Log</div>&nbsp;&nbsp;<a href=\"" + rowData.error_log + "\" target=_blank>" +
				  "<img style=\"vertical-align:text-middle;\" src=\"themes/icons/bomb_16.png\"></a><br></br>";
	
	for (var key in rowData) {
		var fields = ["time", "name", "address", "description", "result", "classname", "trace_log", "error_log"];
		
		if(jQuery.inArray(key, fields) == -1) {
			// for each device
			for (var i in rowData[key]) {
				for (var prop in rowData[key][i]) {
					if (prop == 'logcat_main') {
						strContent += "<div class=title>" + key + "</div>&nbsp;&nbsp;<a href=\"" + rowData[key][i][prop] + "\" target=_blank>" + 
									  "<img style=\"vertical-align:text-bottom;\" src=\"themes/icons/logcat_16.png\"></a></br>";
					} else {
						// show screen shots
						strContent += "<img style=\"height:auto;width:auto;max-width:600px;max-height:600px;\" src=\"" + rowData[key][i][prop] + 
									  "\" /></br>";
					}
				}
			}
			
			strContent += "<br>";
		}
	}
	
	$('#content').html(strContent);
}

function checkDevice(hardware) {
	var file = $('#comboTestjobRun').combobox('getText');
	
	if (file) {
		$.ajaxSetup({cache: false});
		$.get('src/testjob/readTestJobFile.php', {file:file}, function(result) {
			if (hardware != result) {
				alert('You specified hardware name as \'' + result + '\' in your test job.\nYou cannot assign the job to this device.');
				$('#lbRunNow').linkbutton('disable');
				$('#lbRunLater').linkbutton('disable');
			} else {
				$('#lbRunNow').linkbutton('enable');
				$('#lbRunLater').linkbutton('enable');
			}
		});
	}
}

function toggleBuildForm(ele) {
	if (ele.checked) {
		$('#testjobBuild').prop('disabled', false);
		$('#testjobUrl').prop('disabled', false);
		$('#testjobBuild').val($('#testjobUrlHidden').val());
		$('#testjobUrl').val($('#testjobBuildHidden').val());
	} else {
		$('#testjobBuild').prop('disabled', true);
		$('#testjobUrl').prop('disabled', true);
		
		// save temporarily
		$('#testjobUrlHidden').val($('#testjobUrl').val());
		$('#testjobBuildHidden').val($('#testjobBuild').val());
		
		$('#testjobBuild').val("");
		$('#testjobUrl').val("");
	}
}

function toggleSaveOption(ck) {
	if (ck.checked) {
		if ($('#testjobLoops').numberspinner('getValue') > 1) {
			$('#passRateSlider').show();
		}
		$('#createCyclePlan').show();
		$('#labelCreateCyclePlan').show();
	} else {
		$('#passRateSlider').hide();
		$('#createCyclePlan').hide();
		$('#labelCreateCyclePlan').hide();
		$('#createCyclePlan').attr('checked', false);
	}
}

function onClickRow(dgName, index) {
	//alert(editIndex + ', ' + index + '\n' + $(dgName).datagrid('getRows')[index]['key']);
	if (editIndex != index) {
		if (endEditing(dgName)) {
			// never let the user to edit the key field of the first row - Custom Script
			if ($(dgName).datagrid('getRows')[index]['key'] == 'Custom Script') {
				$(dgName).datagrid('getColumnOption', 'key').editor = null;
				$(dgName).datagrid('getColumnOption', 'value').editor = 'text';
			} else {
				$(dgName).datagrid('getColumnOption', 'key').editor = 'text';
			}
			
			$(dgName).datagrid('selectRow', index).datagrid('beginEdit', index);
			editIndex = index;
		} else {
			$(dgName).datagrid('selectRow', editIndex);
		}
	}
}

function endEditing(dgName) {
	if (editIndex == undefined) {return true;}
	if ($(dgName).datagrid('validateRow', editIndex)) {
		var ed = $(dgName).datagrid('getEditor', {index:editIndex,field:'key'});
		var key;
		
		if (ed) {
			key = $(ed.target).text();
			$(dgName).datagrid('getRows')[editIndex]['key'] = key;
		}
				
		$(dgName).datagrid('endEdit', editIndex);
		editIndex = undefined;
		return true;
	} else {
		return false;
	}
}

function acceptEditRow(dgName) {
	if (endEditing(dgName)) {
		$(dgName).datagrid('acceptChanges');
	}
	
	// save setting json file under the repo directory
	$.post('src/testjob/saveSettings.php', {machine:curClientId,data:$(dgName).datagrid('getRows'),userid:username}, function(result) {
		//alert(result);
	});
}

function appendRow(dgName) {
	if (endEditing(dgName)) {
		$(dgName).datagrid('appendRow', {key:'', value:''});
		editIndex = $(dgName).datagrid('getRows').length - 1;
		
		if ($(dgName).datagrid('getRows')[editIndex]['key'] == 'Custom Script') {
			$(dgName).datagrid('getColumnOption', 'key').editor = null;
		} else {
			$(dgName).datagrid('getColumnOption', 'key').editor = 'text';
		}
		
		$(dgName).datagrid('selectRow', editIndex).datagrid('beginEdit', editIndex);
	}
}

function removeRow(dgName) {
	if (editIndex == undefined) {return;}
	
	if ($(dgName).datagrid('getRows')[editIndex]['key'] != 'Custom Script') {
		$(dgName).datagrid('cancelEdit', editIndex).datagrid('deleteRow', editIndex);
		editIndex = undefined;
	} else {
		alert('Cannot delete this row!');
		$(dgName).datagrid('cancelEdit', editIndex);
		editIndex = undefined;
		return;
	}
}

function browseFile(dgName) {
	var selRow = $(dgName).datagrid('getSelected');
	
	if (selRow) {
		editIndex = $(dgName).datagrid('getRowIndex', selRow);
		$(dgName).datagrid('beginEdit', editIndex);
		$('#dlgSettings').dialog('open');
	}
}

function saveFilePath(dgName) {
	var file = $('#dgFileBrowser').datagrid('getSelected')['name'];
	var path = $('#dlgSettings').dialog('options').title;

	var ed = $(dgName).datagrid('getEditor', {index:editIndex,field:'value'});
	
	if (ed) {
		 $(ed.target).val(path + '/' + file);
	}
}