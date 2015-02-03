/*
 * Document Ready
 * All javascript will be here
 */
var columnWidth = 300;
var curField;
var curRow;
var editIndex = undefined;

$(document).ready(function() {
	
	/**
	 * radio buttons - View by
	 */
	$(".cb-enable").click(function(){
		var parent = $(this).parents('.switch');
		$('.cb-disable',parent).removeClass('selected');
		$(this).addClass('selected');
		$('.checkbox',parent).attr('checked', true);
	});
	
	$(".cb-disable").click(function(){
		var parent = $(this).parents('.switch');
		$('.cb-enable',parent).removeClass('selected');
		$(this).addClass('selected');
		$('.checkbox',parent).attr('checked', false);
	});
	
	/*
	 * Global variable
	 */
	//var lastIndex;
    //var cycleplan_data;    //variable holds the value for cycle plans
    //var data = {"total":0, "rows":[]}; // data to be used for ScriptQueue datagrid - Jung Soo Kim
    var scriptQueueSelectedField = null; // field of the selected cell in scriptQueue datagrid - Jung Soo Kim
	var scriptQueueSelectedRowIndex = null; // field of the selected row index in scriptQueue datagrid - Jung Soo Kim
	//var comboMultipleSelection = []; // array of multiple selections from combo box

    fitWindowSize();
    //MakeRequestFramework();
    
    /*
     * Dragging the cloned function
     * Jung Soo Kim
     * March 21, 2012
     */
    $('.dragContainer').draggable({
    	revert:true,
    	proxy:'clone',
    	onStartDrag:function(){
    		$(this).draggable('options').cursor = 'not-allowed';
    		$(this).draggable('proxy').css('z-index', 10);
    	},
    	onStopDrag:function(){
    		$(this).draggable('options').cursor = 'move';
    	}
    });
    
    /*
     * Dropping the cloned function to the execution queue
     * Jung Soo Kim
     * Mar-21-2012
     */
    $('.dropContainer').droppable({
    	onDragEnter:function(e, source){
    		$(source).draggable('options').cursor = 'auto';
    	},
    	onDragLeave:function(e, source){
    		$(source).draggable('options').cursor = 'not-allowed';
    	},
    	onDrop:function(e, source){
    		if (scriptQueueSelectedRowIndex != null && scriptQueueSelectedField != null) {
    			editScript('');  // open edit box on dropping method help panel
    		} else {
    			alert('Please select a cell first!');
    		}
    	}
    });
    
    /*
     * mtree
     * Jung Soo Kim
     * Mar 26, 12
     * This is a tree jqeury that asynchronously retrieve member functions per classes from the member function 
     * database
     */
    /*
    $("#mtree").tree({
    	onClick:function(node){
    		if ($(this).tree('isLeaf', node.target)) {
    			if (bEdit.text == 'Save') {
    				alert('Please save the changes of test API');
    				
    				var id = $('#idMethod').text();
    				var node = $('#mtree').tree('find', id);
    				$('#mtree').tree('select', node.target);  // back to the test API node which is in edit mode.
    			} else {
        			//var divContent = '';
        			var parent = $(this).tree('getParent', node.target);
        			
        			/*
        			divContent = divContent + '<img src="themes/icons/invader64.png" />';
        			divContent = divContent + '<p class="function">' + node.text + '</p>'; 
        			divContent = divContent + '<p class="class">' + parent.text + '</p>';
        			
        			
        			divContent = divContent + '<p class="testId" hidden>' + node.id + '</p>';
        			divContent = divContent + '<img alt="" src="themes/icons/box_closed.png" /><p class="class">' + parent.text + '</p>';
    				divContent = divContent + '<img alt="" src="themes/icons/Application.png" /><p class="function">' + node.text + '</p>';
    				divContent = divContent + '<p>Description</p><element class="description">' + node.attributes.description.replace(/\n/gi, '<br>') + '</element>';
    				divContent = divContent + '<p>Example</p><element class="example">' + node.attributes.example.replace(/\n/gi, '<br>') + '</element>';
    				
    				parseMethodInfo(parent.text, node.text, node.attributes.description.replace(/\n/gi, '<br>'), node.attributes.example.replace(/\n/gi, '<br>'));
    				
    				var tab = $('#scriptTabs').tabs('getSelected');
    				
    				if (tab.panel('options').title == 'Test API Detail View') {
    					$('#scriptTabs').tabs('select', tab.panel('options').title);
    				}
    			}    			
    		}
    	}
    });*/
    
    /*
     * scriptQueue Datagrid
     * Jung Soo Kim
     * March 26, 12
     * functions, event handers & toolbar handlers
     */
    /*
    $("#scriptQueue").datagrid({
    	fitColumns:true,
    	singleSelect:true,
    	rownumbers:true,
    	fit:true,
    	border:0,
    	columns:[[{
    		field:'target',
    		title:'Target Device',
    		width:140,
    		styler:function(value, row, index) {
    			if (index == scriptQueueSelectedRowIndex && scriptQueueSelectedField == 'target') {
    				return 'background-color:orange;color:maroon;';
    			}
    		}
    	},{
    		field:'companion',
    		title:'Companion Device',
    		width:140,
    		styler:function(value, row, index) {
	    		if (index == scriptQueueSelectedRowIndex && scriptQueueSelectedField == 'companion') {
					return 'background-color:orange;color:maroon;';
				}
    		}
    	}]],
    	toolbar:[{
    		id:'btsave',
    		text:'Save',
    		iconCls:'icon-save',
    		handler:function(){
    			var tcName = document.getElementById('testcaseid').innerHTML;
    			var tcDesc = delDQuote(document.getElementById('testcasedesc').innerHTML); // delete double quotes from the string.
    			
    			if (tcName) {
    				var rows = $('#scriptQueue').datagrid('getRows');
    				
    				if (rows.length > 0) {
    					var list = [];
        				
        				// get all rows
        				for (var i = 0; i < rows.length; i++) {
        					if (rows[i].target.length > 0 || rows[i].companion.length > 0) {
        						list.push({
            						'target':rows[i].target,
            						'companion':rows[i].companion
            					});
        					}
        				}
        				
        				// save to a file
        				$.post('src/testscript/saveTestScript.php?testname=' + tcName + '&testdesc=' + tcDesc, {list:list}, function(result){
        					if (!result) {
        						alert(result);
        					}
        				});
        				
        				// reload parent node of the tree
        				var node = $('#treeTestCase').tree('getSelected');
        				var parent = $('#treeTestCase').tree('getParent', node.target);
        				
        				if (parent) {
        					$('#treeTestCase').tree('reload', parent.target);
        				} else {
        					$('#treeTestCase').tree('reload');
        				}
    				} else {
    					alert('Create test script first!');
    				}
    			} else {
    				alert('Please select test case first!');
    			}
    		}
    	},{
    		id:'btedit',
    		text:'Edit',
    		iconCls:'icon-edit',
    		handler:function(){
    			if (data.rows.length > 0) {
        			var rowData = data.rows[scriptQueueSelectedRowIndex];	// get the row data of the selected cell
        			var cellData;											// data of the selected cell
        			
        			switch (scriptQueueSelectedField) {
        			case 'target':
        				cellData = rowData.target;
        				break;
        			case 'companion':
        				cellData = rowData.companion;
        			}
        			
        			editScript(cellData);  // open dialog box to edit values for each parameter
    			}
    		}
    	},{
    		id:'btdelete',
    		text:'Delete',
    		iconCls:'icon-delete',
    		handler:function(){
    			var tcName = document.getElementById('testcaseid').innerHTML;
    			
    			if (tcName) {
    				$.messager.confirm('invader+', 'Do you want to delete the file ' + tcName + '.xml?', function(response) {
    					if (response == true) {
    						$.post('src/testscript/deleteTestScript.php?filename=' + tcName, function(){
    	    					// clear the content
    	    					data = {"total":0, "rows":[]};
    	    					$('#scriptQueue').datagrid('loadData', data);
    	    					
    	    					// refresh the selected node
    	    					// reload parent node of the tree
    	        				var node = $('#treeTestCase').tree('getSelected');
    	        				var parent = $('#treeTestCase').tree('getParent', node.target);
    	        				
    	        				if (parent) {
    	        					$('#treeTestCase').tree('reload', parent.target);
    	        				} else {
    	        					$('#treeTestCase').tree('reload');
    	        				}
    	    				});
    					}
    				})
    			}
    		}
        },{
    		id:'btAdd',
    		text:'Add',
    		iconCls:'icon-row-add',
    		handler:function(){
	    		// if field is not defined, set to target field by default
				if (scriptQueueSelectedField == undefined) {
					scriptQueueSelectedField = 'target';
				}
				
				if (data.rows.length == 0 || scriptQueueSelectedRowIndex == undefined) {
					// append a row
    				$('#scriptQueue').datagrid('appendRow', {
    					target:'',
    					companion:''
    				});
    				
					scriptQueueSelectedRowIndex = data.rows.length - 1;
				} else {
					// always insert a row after the selected cell
    	    		$('#scriptQueue').datagrid('insertRow', {
    					index: ++scriptQueueSelectedRowIndex,
    					row:{
    						target:'',
    						companion:''
    					}
    				});
				}
    			
    			// refresh datagrid
    			$('#scriptQueue').datagrid('loadData', data);
    		}
    	},{
    		id:'btremove',
    		text:'Remove',
    		iconCls:'icon-remove',
    		handler:function(){
    			var selected = $('#scriptQueue').datagrid('getSelected');
    			if (selected){
    				var index = $('#scriptQueue').datagrid('getRowIndex', selected);
    				$('#scriptQueue').datagrid('deleteRow', index);
    				
    				// decrease the selected cell's row index by 1 if deleted row is before the selected cell
    				if (scriptQueueSelectedRowIndex > index) {
    					scriptQueueSelectedRowIndex--;
    				} else if (scriptQueueSelectedRowIndex == index) {
    					scriptQueueSelectedRowIndex = undefined;
    					scriptQueueSelectedField = undefined;
    				}
    			} else {
    				var cell = data.rows[scriptQueueSelectedRowIndex];
    				
    				// clear the selected cell only
    				switch (scriptQueueSelectedField) {
    				case 'target':
    					cell.target = ''
    					break;
    				case 'companion':
    					cell.companion = ''
    					break;
    				}
    			}
    			
    			// refresh datagrid
				$('#scriptQueue').datagrid('loadData', data);
    		}
    	},{
    		id:'btclear',
    		text:'Clear',
    		iconCls:'icon-clear',
    		handler:function(){
    			// clear the content
				data = {"total":0, "rows":[]};
				
				// delete the selected cell row index and field
				scriptQueueSelectedRowIndex = undefined;
				scriptQueueSelectedField = undefined;
				
				$('#scriptQueue').datagrid('loadData', data);
    		}
    	},{
                id:'btrun',
                text:'Run',
                modal:'false',
                iconCls:'icon-playback',
                handler:function(){
                        var testname = document.getElementById('testcaseid').innerHTML;
                        if (testname) {
                                var efile = "tempdata/single_log_data/"+username+"_err.txt";
                                var rfile = "tempdata/single_log_data/"+username+"_log.txt";
                                $('#win').window('open');
                                $.post('src/execution/singleRun.php', {testname:testname, username:username}, function(result){
                                        $('#win').window('close');
                                        if(result.msg == "success"){
                                                alert("Result log file will be displayed!");
                                                popup(rfile);
                                        }else{
                                                if(result.msg == "error"){
                                                    alert("Error log file will be displayed!");
                                                    popup(efile);
                                                }else{
                                                    alert(result.msg);
                                                }
                                        }
                                },'json');
                        }else{
                            alert('Please select test case first!');
                        }
                }
        },{
                id:'btstop',
                text:'Stop',
                iconCls:'icon-stop',
                handler:function(){
                        var testname = document.getElementById('testcaseid').innerHTML;
                        if (testname) {
                        	var win = $.messager.progress({
                            	title:'Please waiting',
                            	text:'Processing...',
                            	interval:'600'
                		});
                                $.post('src/execution/singleStop.php', {username:username}, function(result){
                                		$.messager.progress('close');
                                        if(result.msg == "success"){
                                                alert("Test process is stopped!");
                                        }else{
                                                alert(result.msg);
                                        }


                                },'json');
                        }else{
                                alert('Please select test case first!');
                        }
                }
	   },{
                id:'btlog',
                text:'View Log',
                iconCls:'icon-view',
                handler:function(){
                        var testname = document.getElementById('testcaseid').innerHTML;
                        if (testname) {
                                var file='../../tempdata/single_log_data/' + username + '_err.txt';
                                var url='tempdata/single_log_data/' + username + '_err.txt';
                                $.post('src/execution/file_exist.php', {file:file, username:username}, function(result){
                                        if(result.msg == "success"){
                                                window.open(url,'Download');
                                        }else{
                                                alert(result.msg);
                                        }


                                },'json');


                        }else{
                                alert('Please select test case first!');
                        }
                }


    	}],
    	onClickCell:function(rowIndex, field, value){
    		scriptQueueSelectedRowIndex = rowIndex;
    		scriptQueueSelectedField = field;
    		$('#scriptQueue').datagrid('loadData', data); // refresh datagrid
    		
    		var selectedRow = data.rows[scriptQueueSelectedRowIndex];	// get selected row
    		
    		switch (scriptQueueSelectedField) {
    		case 'target':
    			updateMethodHelpPanel(selectedRow.target);
    			break;
    		case 'companion':
    			updateMethodHelpPanel(selectedRow.companion);
    			break;
    		}
    	}
    }); */
    
    /*
     * dlgMethod
     * Jung Soo Kim
     *//*
    $('#dlgMethod').dialog({
    	title:'Test API Method',
    	width:600,
    	height:700,
    	closed:true,
    	modal:true,
    	buttons:[{
    		text:'OK',
    		iconCls:'icon-ok',
    		plain:true,
    		handler:function(){
    		}
    	},{
    		text:'Cancel',
    		iconCls:'icon-cancel',
    		plain:true,
    		handler:function(){
    			$('#dlgMethod').dialog('close');
    		}
    	}]
    });*/
    
    /*
     * scriptEditDlg
     * Jung Soo Kim
     * functions and constructor of scriptEditDlg
     * Mar 27, 12
     */
    /*
    $('#scriptEditDlg').dialog({
    	title:'Edit Script Parameter Value',
    	width:600,
    	height:400,
    	closed:true,
    	modal:true,
    	buttons:[{
    		text:'OK',
    		iconCls:'icon-ok',
    		plain:true,
    		handler:function(){
    			var updateRow = data.rows[scriptQueueSelectedRowIndex];	// get the row of the highlighted cell to be updated
    			var updateString = getParameterValue();			 	// get the parameters and values from the editor 
    			
    			switch (scriptQueueSelectedField) {
    			case 'target':
    				updateRow.target = updateString;  // update the highlighted cell with edited values (target)
    				break;
    			case 'companion':
    				updateRow.companion = updateString;  // update the highlighted cell with edited values (companion)
    			}
    			
    			$('#scriptQueue').datagrid('loadData', data);  // refresh datagrid to update the postings
    			$('#scriptEditDlg').dialog('close');  // close the dialog box
    		}
    	},{
    		text:'Cancel',
    		iconCls:'icon-cancel',
    		plain:true,
    		handler:function(){
    			$('#scriptEditDlg').dialog('close');
    		}
    	}]
    });*/
    
    /*
     * tcDetailTable datagrid
     * Jung Soo Kim
     * June 20, 2013
     */
    $('#tcDetailTable').datagrid({
    	rownumbers:true,
    	fitColumns:true,
    	fit:true,
    	border:0,
    	nowrap:false,
    	singleSelect:true,
    	onBeforeEdit:function(index, row) {
    		for(var key in row) {
    			row[key] = row[key].replace(/<br>/g, '\n');
    		}
    	},
    	onAfterEdit:function(index, row) {
    		var content = {};
    		
    		for (var key in row) {
    			content[key] = row[key].replace(/\n/g, '<br>');
    		}
    		
    		$(this).datagrid('updateRow',{
    			index:index,
    			row:content
    		});
    	},
    	onClickCell:function(rowIndex, field, value) {
    		// set readonly Description field
    		setReadOnly($('#description'));
    		
    		// end edit for the preivously selected row
    		$(this).datagrid('endEdit', curRow);
    		
    		curField = field;
    		curRow = rowIndex;
    		
    		// start begin editing
    		$(this).datagrid('beginEdit', rowIndex);
    	}
    });
    
    /*
     * layoutScript handler
     * Jung Soo Kim
     * July 12, 2013
     */
    $('#layoutScript').layout('panel', 'center').panel({
    	onResize:function(width, height) {
    		resizePanelBody($(this));
    	}
    });
    
    /*********************************************************************
     * $('#searchTreeTestCase').tree
     */
    $('#searchTreeTestCase').tree({
    	onSelect:function(node) {
    		onSelectHandler(this, node);
    	},
    	
    	onContextMenu:function(e, node) {
			onContextMenuHandler(e, this, node);
    	}
    });

    /***********************************************************************************************************
     * treeTestCase
     * Tree Easy UI JQuery
     * Jung Soo Kim
     * April 11, 12
     */
    $("#treeTestCase").tree({
		// To append nodes/corresponding test cases on click of test suite nodes
		onSelect:function(node) {
    		onSelectHandler(this, node);
		},
		
		onContextMenu:function(e, node) {
			onContextMenuHandler(e, this, node);
		}
	});
    
    
     /* Support multiple devices on show progress at Run page
      *
      * Jing-May
      */
      $("#device_dg").datagrid({
                onClickRow:function(rowIndex, rowData){
                        $('#button_cp').linkbutton('enable');
                        $('#runlist').datagrid('reload');
                }
      });
      
    /**
     * searchTreeTestPlan
     * Jung Soo Kim
     */
    $('#searchTreeTestPlan').tree({
    	fit:true,
    	onClick:function(node) {
    		treeTestPlanOnClickHandler(node);
    	}
    });

    /************************************************************************************
     * treeTestPlan
     * Tree EasyUI JQeury elements
     * Jung Soo Kim
     */
	$("#treeTestPlan").tree({
		fit:true,
		onClick:function(node){
			treeTestPlanOnClickHandler(node);
		}
		/* we don't need this
		onExpand:function(node) {
			// select the node first
			$(this).tree('select', node.target);
			var node = $(this).tree('getSelected');
			
			openRunlist(node);
		},*/
	
		/* we don't need this for now
		onCheck:function(node, checked){			
			if(node.id.indexOf("cycle^") > -1){
				var children = $(this).tree('getChildren', node.target);
				
				if(children.length > 0){
					if(checked){
    					for(var i = 0; i < children.length; i++){
    						if(children[i].iconCls == "icon-script"){
    							$(this).tree('check', children[i].target);
    						}
    					}
    				} else {
    					// uncheck all the checked children
    					for(var i = 0; i < children.length; i++){
    						if(children[i].checked){
    							$(this).tree('uncheck', children[i].target);
    						}
    					}
    				}
				} else {
					// check is possible only if test cases node is available
					$(this).tree('uncheck', node.target);
				}
			} else if(node.id.indexOf("group^") > -1){
				if(checked){
					$(this).tree('uncheck', node.target);
				}
			} else if(node.id.indexOf("master^") > -1){
				if(checked){
					$(this).tree('uncheck', node.target);
				}
			} else {
				if(checked && node.iconCls != 'icon-script'){
					$(this).tree('uncheck', node.target);
				}
			}
		},*/
		
		/* we don't need this for now
		onContextMenu: function(e, node){
			e.preventDefault();
			
			// select node first before loading context menu
			$(this).tree('select', node.target);
			
			// if cycle node is selected, show mm2
			if (node.id.indexOf('cycle^') != -1) {
				// show Context menu
				$("#mm2").menu({
					onClick:function(item){
						if (item.text == 'Refresh') {
							$('#treeTestPlan').tree('reload', node.target);
						} else if (item.text == 'Add to Runlist') {
							addRunlist(); // add selected test cases to Runlist
						}
					}
				}).menu('show', {
					left:e.pageX,
					top:e.pageY
				});
			} else {
				// show Context menu
				$("#mm1").menu({
					onClick:function(item){
						if (item.text == 'Refresh') {
							$('#treeTestPlan').tree('reload', node.target);
						}
					}
				}).menu('show', {
					left:e.pageX,
					top:e.pageY
				});
			}
		}*/
	});
	
	/**
	 * tcExecDatagrid
	 * Datagrid to load test cases on clicking cycle plan
	 * Jung Soo Kim
	 * Aug 15, 2013
	 */
	var testResultJson = [{
		 "result":'P',
		 "text":'P'
	 },{
		 "result":'F',
		 "text":'F'
	 },{
		 "result":'B',
		 "text":'B'
	 },{
		 "result":'I',
		 "text":'I'
	 }];
	
	$("#tcExecDatagrid").datagrid({
		fit:true,
		rownumbers:true,
		fitColumns:true,
		singleSelect:false,
		border:false,
		pagination:true,
		pageNumber:1,
		pageSize:50,
		pageList:[10, 20, 30, 40, 50],
		columns:[[
			{field:'ck', checkbox:true},
			{field:'testCaseName', title:'testCaseName', width:300},
			{field:'testResult', title:'testResult', width:70, styler:cellStyler, 
			 editor:{type:'combobox', options:{data:testResultJson, valueField:'result', textField:'text'}}},
			{field:'defectReportId', title:'defectReportId', width:150, editor:{type:'text'}},
			{field:'blockedReason', title:'blockedReason', width:300, editor:{type:'text'}},
			{field:'executionMethod', title:'executionMethod', width:110},
			{field:'groupTypeValue1', title:'groupTypeValue1', width:100},
			{field:'comments', title:'comments', width:240, editor:{type:'text'}},
			{field:'lastUpdUser', title:'lastUpdUser', width:180},
			{field:'lastUpdDate', title:'lastUpdDate', width:180}
		]],
		onClickRow:function(rowIndex, rowData) {
			editRow(rowIndex);
		},
		onBeforeLoad:function() {
			//updateTestResults();
		},
		onAfterEdit:function(rowIndex, rowData, changes) {
			//alert(rowIndex + '\n' + rowData['testCaseName'] + '\n' + changes.toSource());
			if (JSON.stringify(changes) != "{}") {
				updateTestResults(rowIndex, changes);
			}
		}
	});

	//--------------------------------------------------------------------------------------
	// Element ID: treeTestPlanReport
	// Author: Jing-May Wang
	//--------------------------------------------------------------------------------------
	$("#treeTestPlanReport").tree({
	    onClick:function(node){
			var is_leaf=$(this).tree('isLeaf',node.target);
		 	
			if (is_leaf){
				$.messager.progress({
					title:'Please waiting',
		            text:'Processing...',
		            interval:'900'
		        });
	
				$.post('src/report/get_tc_info_cycle.php',{name:node.text},function(result){
		    		if (result.msg == "Empty info from Test Central") {
		    			alert(result.msg);
		    		} else {
		    			show_summary_cycle(result);  
		    		}
				$.messager.progress('close');
				},'json');
			} else{
		       $.post('src/report/get_tc_info_master.php',{name:node.text},function(result){
		    	   if (result.msg == "Empty info from Test Central") {
		    		   alert(result.msg);
	
		    	   } else {
		            	show_summary_master(result);  
		    	   }
		       },'json');
			}
	    }
	});

	/**************************************************************************************
	 * mainTabs EasyUI JQuery Tabs element
	 * Jung Soo Kim
	 */
	$("#mainTabs").tabs({
		fit:true,
		onSelect:function(title){
		    $("#layoutExec").layout({
				width:$(this).width()
		    });
		    $("#layoutScript").layout({
				width:$(this).width()
		    });
		    
		    // For configuration fix about all buttons in one level instead of 2
            $('#SelectAll').show();
            $('#UnSelectAll').show();
            $('#testrun').combobox({valueField:'id',textField:'name',data:[{id:'yes',name:'yes',selected:true},{id:'no',name:'no'}] });

            flag_configure=1;
            
            $("#tctable").datagrid('showColumn','ck'); 
		    // end
		    
            $("#layoutRun").layout({
				width:$(this).width()
		    });
            
		    $("#layoutTC").layout({
				width:$(this).width()
		    });
		    
		    $("#layoutReport").layout({
				width:$(this).width()
		    });
		    
		    //MakeRequestFramework();
		    //selectLoadData();
		    //selectLoadData2();   
		}
	});
	
	/**************************************************************************************
	 * scriptTabs 
	 * EasyUI JQeury Tabs element
	 * Jung Soo Kim
	 * March 21, 12
	 */
	/*
	$("#scriptTabs").tabs({
		onSelect:function(title){
			switch (title) {
			case 'Script Editor':
				var tcName = document.getElementById('testcaseid').innerHTML;
				
				// make sure to fit the datagrid to the space
				$("#scriptQueue").datagrid({
					width:$(this).width
				});
				
				if (!tcName) {
					alert('Please select test case first!');
					$(this).tabs('select', 'Test Case');  //return to the first tab.
				}
				break;
			case 'Script Viewer':
				var tcName = document.getElementById('testcaseid').innerHTML;
				
				// open test script XML file
				if (tcName) {
					$.post('src/testscript/writeTempTestScriptXML.php?filename=' + tcName, function(result){
				 		if(result == "SUCCESS"){
				 			LoadXML('XMLHolder', 'tempdata/testscript.xml'); // load corresponding test script XML
				 		} else {
				 			$('#XMLHolder').html(''); // empty 
				 		}
				 	});
				}
				break;
			case 'Test API Detail View':
				var idFramework = document.getElementById('cc').value;  // framework name
				var nMethod = $('#mtree').tree('getSelected');  // get method node
				
				if (nMethod) {
					if ($('#mtree').tree('isLeaf', nMethod.target)) { 
						var nPackage = $('#mtree').tree('getParent', nMethod.target); // if selected node is not method, get Package node
						
						loadTestAPIInfo(idFramework, nPackage.text, nMethod.id, nMethod.text);  // load form
					}
				}	
				break;
			}
		}
	});*/
	
	/*
	 * Test API Detail View panel functions
	 * Jung Soo
	 */
	/*
	$('#scriptTabs').tabs('getTab', 'Test API Detail View').panel({
		onResize:function(width, height){
			resizeTestAPIInfo();  // resize
		}
	});*/
	
	/*
	 * select2
	 * Combobox
	 * Jing-May
	 */
	$("#select2").combobox({
		onChange : function() {
			var execlist = $('#select2').combobox('getValue');
			
			if ( execlist == "--Select an execution from list--" || execlist == "default" || execlist == null){
			} else {
    			$.post('src/execution/update_detail.php',{execlist:execlist}, function(result){
            			$('#testcase_dg').datagrid('loadData',result);// load the data to dev panel
    			},'json');
			}
		}       
	});   

    /*
     * selResult
     * Combobox
     * Jing-May
     */
    $("#selResult").combobox({
    	onSelect : function() {
			var reValue = '';
			var seValue = '';
			var indexRow = '';
            
			reValue = $('#selResult').combobox('getValue');
			seValue = $('#tcExecDatagrid').datagrid('getChecked');
            
			if ( reValue == ''){
				alert("You didn't select a result to set!");
				exit;
			}
		
			if(seValue == '') {
				alert("No test case is selected, so no result is changed");
			} else {
				//alert(seValue.length);
    			for (var i = 0; i < seValue.length; i++) {
    				indexRow = $('#tcExecDatagrid').datagrid('getRowIndex', seValue[i]);
    				//alert(indexRow);
    				$('#tcExecDatagrid').datagrid('updateRow',{
    					index:indexRow,
    					row: {
							testResult: reValue
						}
    				});
    				
    				updateTestResults(indexRow,'none');
            	}

    			$('#tcExecDatagrid').datagrid('uncheckAll');

    			//alert(seValue[0]['testCaseName']);
    			//alert(seValue[1]['testCaseName']);
			}
		}
    });

	/*
	$("#viewall").combobox({
		onChange : function() {
			var view = $('#viewall').combobox('getValue');
				if (view == "yes"){
					$('#select2').combobox({
						url:'src/execution/get_result_plan_name.php?user_name=all',
						valueField:'id',
						textField:'text'
					});
	
				} else {
					$('#select2').combobox({
						url:'src/execution/get_result_plan_name.php?user_name='+username,
						valueField:'id',
						textField:'text'
					});
				}
		}
	});*/
	
	// Test Plan Form Components
	$('#testplanAccordion').accordion({
		fit:true
	});
	
	$('#groupCombo').combobox({
		width:'300', 
		valueField:'id', 
		textField:'text', 
		url:'src/testplan/get_grouplist.php?user_name='+username,
	        onChange : function() {

                var selected_id = $('#groupCombo').combobox('getValue');
                var data_array = $('#groupCombo').combobox('getData');
                var num = selected_id;
                var group = data_array[num]["text"];

            	if ( group == "--Select group--" || group == "default" || group ==null){
            		
            	} else {
                    	var win = $.messager.progress({
                            	title:'Please wait',
                            	text:'Processing...',
                            	interval:'900'
                    	});
                    	$.post('src/testplan/get_tc_product.php',{group:group},function(result){
                            	if (result.msg == "Empty info from Test Central") {
                                    	alert(result.msg);
                            	} else {
                                    	$('#productCombo').combobox('loadData', result);
                            	}
                            	$.messager.progress('close');
                    	},'json');
            	}
        	}


	});
	
	$('#groupCombo2').combobox({
		width:'300', 
		valueField:'id', 
		textField:'text', 
		url:'src/testplan/get_grouplist.php?user_name='+username,
	        onChange : function() {

                var selected_id = $('#groupCombo2').combobox('getValue');
                var data_array = $('#groupCombo2').combobox('getData');
                var num = selected_id;
                var group = data_array[num]["text"];

                	if ( group == "--Select group--" || group == "default" || group ==null){
                	} else {
                        	var win = $.messager.progress({
                                	title:'Please wait',
                                	text:'Processing...',
                                	interval:'900'
                        	});
                        	$.post('src/testplan/get_tc_master_plan.php',{group:group},function(result){
                                	if (result.msg == "Empty info from Test Central") {
                                        	alert(result.msg);
                                	} else {
                                        	$('#masterCombo').combobox('loadData', result);
                                	}
                                	$.messager.progress('close');
                        	},'json');
                	}
        	}


	});
	
	$('#productCombo').combobox({
		width:'300', 
		valueField:'id', 
		textField:'text', 
		url:'tempdata/product_default.json'
	});
	
	$('#masterCombo').combobox({
		width:'700', 
		valueField:'id', 
		textField:'text', 
		url:'tempdata/product_default.json'

	});

        $('#workingPlan').combobox({
                width:'700',
                valueField:'id',
                textField:'text',
                url:'src/testplan/get_working_plan.php?user_name='+username,
		onSelect : function() {
                        var selected_plan = $('#workingPlan').combobox('getText');
			var data = {"total":0, "rows":[]};

                        if ( selected_plan == "--Select a working plan--" || selected_plan == "default" || selected_plan == null){
                               	$('#testplanDatagrid').datagrid('loadData',data);

                        } else {
                        $.post('src/testplan/get_local_tests.php',{plan:selected_plan,username:username}, function(result){
				if(result.total == 0){
				
                               		$('#testplanDatagrid').datagrid('loadData',data);
				}else{
                                	$('#testplanDatagrid').datagrid('loadData',result);
				}
                        },'json');
                        }
                }
        });



	
	/*
	 * Test Plan datagrid
	 */
	$('#testplanDatagrid').datagrid({
		border:false,
		fitColumns:true,
		fit:true,
		singleSelect:false,
		rownumbers:true,
		columns:[[
			{field:'name', title:'Name', width:100},
			{field:'description', title:'Description', width:100}
		]]
	});
	
	/*
	 * layoutTPContainer
	 */
	$('#layoutTPContainer').layout({
		fit:true
	});
	
	/*
	 * searchTcNavTree
	 */
	$('#searchTcNavTree').tree({
		checkbox:true,
		onContextMenu:function(e, node) {
			onContextMenuHanderPlan(e, this, node);
		}
	});
	
	/*
	 * tcNavTree: test case navigation tree for test plan
	 */
	$('#tcNavTree').tree({
		checkbox:true,
		onContextMenu:function(e, node) {
			onContextMenuHanderPlan(e, this, node);
		}
	});
	
	$('#searchDev').searchbox({
		width:210,
		prompt:'Search test suite',
		searcher:function(value) {
			search('#treeTestCase', '#searchTreeTestCase', this, value);
		}
	});
	
	$('#searchPlan').searchbox({
		width:210,
		prompt:'Search test suite',
		searcher:function(value) {
			search('#tcNavTree', '#searchTcNavTree', this, value);
		}
	});
	
	$('#searchResult').searchbox({
		width:210,
		prompt:'Search test plan',
		searcher:function(value) {
			search('#treeTestPlan', '#searchTreeTestPlan', this, value);
		}
	});
	
	$('#dgFuncSelect').datagrid({
		fit:true,
		border:false,
		singleSelect:true,
		fitColumns:true,
		nowrap:false,
		columns:[[
		          {field:'function', title:'Function', width:200},
		          {field:'description', title:'Description', width:300}
		]],
		onSelect:function(rowIndex, rowData) {
			updateScriptPath(rowData);
		}
	});
	
	// display the test plan name for execution tab
	parseExectestPlanName();
	
	/*
	 * initialize file select dialog
	 */
	if (window.File && window.FileList && window.FileReader) {
		init();
	}
});

/**
 * initialization of file selector
 * @return
 */
function init() {
	var fileselect = $id("fileselect");
	var filedrag = $id("scriptLoc");
	
	// file select
	//fileselect.addEventListener("change", fileSelectHandler, false);
	
	// is XHR2 available?
	var xhr = new XMLHttpRequest();
	
	if (xhr.upload) {
		// file drop
		filedrag.addEventListener("dragover", fileDragHover, false);
		filedrag.addEventListener("dragleave", fileDragHover, false);
		filedrag.addEventListener("drop", fileSelectHandler, false);
		filedrag.style.display = "block";
	}
}

function fileSelectHandler(e) {
	// cancel event and hover styling
	fileDragHover(e);
	
	// fetch FileList object
	var files = e.target.files || e.dataTransfer.files;
	
	// process all File objects
	for (var i = 0, f; f = files[i]; i++) {
		parseFile(f);
	}
}

function fileDragHover(e) {
	e.stopPropagation();
	e.preventDefault();
	e.target.className = (e.type == "dragover" ? "hover" : "");
}

// output file information
function parseFile(file) {
	/*output(
		"<p>File information: <strong>" + file.name +
		"</strong> type: <strong>" + file.type +
		"</strong> size: <strong>" + file.size +
		"</strong> bytes</p>"
	);*/
	
	// display text
	if (file.type.indexOf("text") == 0) {
		var reader = new FileReader();
		reader.onload = function(e) {
			parseFunctions(file.name, e.target.result);
		}
		
		reader.readAsText(file);
	}
}

function parseFunctions(filename, content) {
	var jsonArray = [];
	var lines = content.split("\n");
		
	for (var i = 0; i < lines.length; i++) {
		var l = lines[i];
		
		// grep class name
		if ((/^class.*:/).test(l)) {
			var className = l.replace(/class/g, '');
			className = className.replace(/\(.*\):/g, '').trim();
		}
		
		if ((/def.*:/).test(l) && (/["']{3}.*["']{3}/).test(lines[i + 1])) {
			var funcName = l.replace(/def/g, '');
			funcName = funcName.replace(/\(.*\):/g, '').trim();
			
			var desc = lines[i + 1].replace(/["']{3}/g, '').trim();
			
			var option = {};
			option['function'] = filename + ':' + className + '.' + funcName;
			option['description'] = desc;
			
			jsonArray.push(option);
			
			/*
			// add panel for each test case function
			$('#aaFunctions').accordion('add', {
				title:filename + ':' + className + '.' + funcName,
				content:'<div style="padding:10px">' + desc + '</div>',
				selected:false
			});
			*/
		}
	}
	
	// load to datagrid
	$('#dgFuncSelect').datagrid({
		data:jsonArray
	});
	
	// show dialog
	$('#dlgScriptFuncSelect').dialog('open');
}

function $id(id) {
	return document.getElementById(id);
}

function output(msg) {
	var m = $id("message");
	m.innerHTML = msg + m.innerHTML;
}

function updateScriptPath(data) {
	var path = $('#scriptLoc').text().match(/.*\//);
	
	path ? $('#scriptLoc').text(path + data['function']) : $('#scriptLoc').text(data['function']);
	
	// re-apply css class
	$('#scriptLoc').addClass("textbox");
	
	// close dialog
	$('#dlgScriptFuncSelect').dialog('close');
}

/**
 * onContextMenuHander
 * @param e
 * @param treeName
 * @param node
 * @return
 */
function onContextMenuHanderPlan(e, treeName, node) {
	
	e.preventDefault();
	
	// show the context menu if the children is test cases
	var children = $(treeName).tree('getChildren', node.target);
	
	if ((children.length > 0 && children[0].id == '2') || node.id == '2') {
		$(treeName).tree('select', node.target);
		
		$('#tcNavTreeMenu').menu({
			onClick:function(item) {
				addTestCaseToPlan(treeName, node);
			}
		});
		
		$('#tcNavTreeMenu').menu('show', {
			left:e.pageX,
			top:e.pageY
		});
	}
}

function addTestCaseToPlan(treeObj, node) {
	var children = [];
	var added = [];
	var data = $('#testplanDatagrid').datagrid('getData');
	
	for (index in data.rows) {
		added.push(data.rows[index].name);
	}
	
	if ($(treeObj).tree('isLeaf', node.target)) {
		$(treeObj).tree('check', node.target);
		node.checked = true;
		children.push(node);
	} else {
		var children = $(treeObj).tree('getChildren', node.target);
	}
	
	for (index in children) {
		var child = children[index];
		
		if (child.checked) {
			if (added.indexOf(child.text) == -1) {
				// add to datagrid
				data.total += 1;
				data.rows.push({
					name:child.text,
					description:child.attributes.description
				});
			}
		}
	}
	
	// reload the updated data
	$('#testplanDatagrid').datagrid('loadData', data);
}

/*
 * parseExecTestPlanName
 */
function parseExectestPlanName() {
	$.ajaxSetup({cache: false});
	$.get('src/runlist/readTestPlanName.php', {user_name:username}, function(planname) {
		$('#tpNameExec').html(planname);
	});
}
    
/*
 * openJira()
 * Jing-May
 */
function openJira(){
	alert("Please file an issue under Component = InvaderPlus");
	window.open("http://idart.mot.com/secure/CreateIssue.jspa?pid=10023&issuetype=5", "target");
}

/*
 * popup function is used by single Run
 * Jing-May
 *
 */
function popup(mylink){
        if (! window.focus)return true;
        var href;
        if (typeof(mylink) == 'string'){
                href=mylink;
        }else{
                href=mylink.href;
        }
        window.open(href, 'log', 'width=400,height=200,scrollbars=yes');
        return false;
}

/*
 * popinfo function is used to display test case info
 * Jing-May
 *
 */
function popinfo(name){
	//alert(name);
	showTestDetail2(name);




}

/*
 * singleStop function is used to stop remote start.sh
 * Jing-May
 *
 */
/*
function singleStop(){
	var win = $.messager.progress({
                title:'Please waiting',
                text:'Processing...',
                interval:'600'
        });

        $.post('src/execution/singleStop.php', {username:username}, function(result){
		$.messager.progress('close');
                if(result.msg == "success"){
                        alert("Test process is stopped!");
                }else{
                        alert(result.msg);
                }


         },'json');

}*/

/**
 * delDQuote
 * Jung Soo Kim
 * July 19, 2012
 * it deletes double quotes from the string
 */
function delDQuote(str) {
	return str = str.replace(/["]/gi, "");
}

/*
 * Author: Madhav
 * Description: adding link to js_functions.js
 */
function addJavascript(jsname,pos) {
	var th = document.getElementsByTagName(pos)[0];
	var s = document.createElement('script');
    
	s.setAttribute('type','text/javascript');
    s.setAttribute('src',jsname);
    th.appendChild(s);
}

addJavascript('src/runlist/js_functions.js','head');

/* 
 * Author: Jing-May
 * Description: adding link to report.js
 */
addJavascript('src/report/report.js','head');
addJavascript('src/execution/execution.js','head');

/*
 * Window Resize event handler
 * Jung Soo Kim
 * Nov 18, 2011
 */
$(window).resize(function() {
	fitWindowSize();
});

/*
 * fitWindowSize()
 * Jung Soo Kim
 * resize the body according to the current browser's window size
 */
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

/***********************************************************************************************************
 * refreshDisplay(element)
 * Display Master plan creation or Cycle plan creation based on the radio button selection
 * Jing-May Wang
 * Oct 3, 2013
 */             
function refreshDisplay(element) {
	if (element == 'Mplan') {
		$('#masterSection').show();
		$('#cycleSection').hide();
	} else if (element == 'Cplan') {
		$('#masterSection').hide();
		$('#cycleSection').show();
	}
}

/**
 * refreshModuleTree(element)
 * Jung Soo Kim
 * July 5, 2012
 * It refreshes module tree based on the selected framework
 */
function refreshModuleTree(element) {
	$('#mtree').tree({
		url:'src/testscript/testlibtree.php?framework=' + element.value
	});
}

/*
 * changeMode
 * Jung Soo Kim
 * June 25, 2013
 * set test case to edit mode or read mode
 */
/*
function toggleMode() {
	var imgName = $('#mode').attr('src');
	
	if (imgName == "img/content_edit.png") {
		editMode();
	} else if (imgName == "img/content_save.png") {
		readMode();
		saveTestCase();
	}
}
*/

/*
function editMode() {
	setEdit('#description');
	setEdit('#PhaseName');
	setEdit('#StatusDescription');
	setEdit('#AutomationStatusDescription');
	setEdit('#FunctionalAreaName');
	setEdit('input[type=text], textarea');
	setMode($('#tcDetailTable'), 'edit');
	$('#mode').attr('src', 'img/content_save.png');
	$('#new').hide();
	$('#addRw').hide();
	$('#removeRw').hide();
	$('#addCol').hide();
	$('#removeCol').hide();
}

function readMode() {
	setReadOnly('#description');
	setReadOnly('#PhaseName');
	setReadOnly('#StatusDescription');
	setReadOnly('#AutomationStatusDescription');
	setReadOnly('#FunctionalAreaName');
	setReadOnly('input[type=text], textarea');
	setMode($('#tcDetailTable'), 'view');
	$('#mode').attr('src', 'img/content_edit.png');
	$('#new').show();
	$('#addRw').show();
	$('#removeRw').show();
	$('#addCol').show();
	$('#removeCol').show();
}
*/

function saveTestCase() {
	//var suite = $('#layoutScript').layout('panel', 'center').panel('options').title;
	//suite = suite.substr(0, suite.indexOf("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"));
	var suite = $('#tcNameDev').html();
	
	if (suite.length > 0) {
		
		// make sure to end edit
		$('#tcDetailTable').datagrid('endEdit', curRow);
		setReadOnly('#description');
		setReadOnly('#scriptLoc');
		
		var testname = suite;
		var tableContent = JSON.stringify($('#tcDetailTable').datagrid('getData'));
		var testDescription = $('#description').text();
		var scriptPath = $('#scriptLoc').text();
		var gitUrl = $('#cbGitSelect').combobox('getValue');
		var execMode = "Manual";
		
		if ($('#scriptLoc').text().length > 0) {
			execMode = "Automated";
		}
		
		if (testname.length == 0) {
			alert("Empty test name");
		} else if (testDescription.length == 0) {
			alert("Empty test description");
		} else if ($('#tcDetailTable').datagrid('getRows').length == 0) {
			alert("Empty test content");
		} else {
			//alert(testname + '\n' + testDescription + '\n' + tableContent);
			tableContent = tableContent.replace(/<br>/g, '\\n');

			// save TestCentral
			$.messager.progress({text:'Saving...'});
			$.post('src/testscript/update_testcase.php', {test_name:testname, test_desc:testDescription, 
				script_path:scriptPath.trim(), test_content:tableContent, exec_mode:execMode, git_url:gitUrl}, function(result) {

				if (result == 5 || result == 6) {
					/*// remember the number of children before reload
					var node = $('#treeTestCase').tree('getSelected');
					
					// if it is for updating existing test case, we don't have to reload the test suite node
					if (node.id == 2) {
						$.messager.progress('close');
					} else {
						var total = $('#treeTestCase').tree('getChildren', node.target).length; 
						
						timerId = setInterval(function() {
							// refresh the node
							$('#treeTestCase').tree('reload', node.target);
							
							// wait 3 seconds before getting the children
							setTimeout(function() {
								var children = $('#treeTestCase').tree('getChildren', node.target);
								alert(node.text + ', ' + children.length + ', ' + total);
								
								if (children.length > total) {
									$('#treeTestCase').tree('select', children[children.length-1].target);
									$.messager.progress('close');
									clearInterval(timerId);
								}
							}, 3000);
						}, 3000);
					}*/
				} else {
					alert(result);
				}
				
				$.messager.progress('close');
			});
			/*var xmlHttp = getXMLHttp1();
			xmlHttp.open("GET", "src/testscript/update_testcase.php?test_name=" + testname + "&test_desc=" + testDescription +
					"&test_content=" + tableContent + "&exec_mode=Manual", false);
			xmlHttp.send(null);
			
			alert('Saved');*/
		}
	}
}

function setEdit(target) {
	
	// make sure to end edit of tcDetailTable datagrid
	$('#tcDetailTable').datagrid('endEdit', curRow);
	
	if ($(target).is("input")) {
		// for input, textarea
		$(target).removeAttr('readonly');
		$(target).css('font-size', '12px');
	} else {
		// for div
		$(target).attr('contenteditable', 'true');
		//$(target).css('border-bottom', '1px solid gray');
	}
	
	//$(target).css('font-family', 'monospace');
	$(target).focus(); // set the focus
}

function setReadOnly(target) {
	updateSaver();
	
	if ($(target).is("input")) {
		$(target).attr('readonly', 'readonly');
		$(target).css('font-size', '12px');
	} else {
		$(target).attr('contenteditable', 'false');
		//$(target).css('border', 'none');
	}
	
	//$(target).css('font-family', 'Arial,Helvetica,sans-serif');
}

function saveDataGrid(target) {
	var data = {"total":0, "rows":[]}; // datagrid data
	var row = {};  // each row object
	var content = $(target).datagrid('getData');
	
	for (var i = 0; i < content.rows.length; i++) {
		var rowContent = content.rows[i];
		
		for (var key in rowContent) {
			row[key] = rowContent[key].replace(/\n/g, '<br>');
		}
		
		data.rows.push(row);
		data.total++;
		row = {};
	}
	
	$(target).datagrid('loadData', data);
}

function setMode(target, mode) {
	var rows = $(target).datagrid('getRows');
	
	for (var i = 0; i < rows.length; i++) {
		if (mode == 'edit') {
			$(target).datagrid('beginEdit', i);
		} else if (mode == 'view') {
			$(target).datagrid('endEdit', i);
		}
	}
}

function addRow() {
	var data = {};
	var fields = $('#tcDetailTable').datagrid('getColumnFields');
	var editIndex = null;
	var selectedIndex = getSelectedRowIndex($('#tcDetailTable'));
	
	for (var i = 0; i < fields.length; i++) {
		data[fields[i]] = '';
	}
	
	$('#tcDetailTable').datagrid('insertRow', {
		index: editIndex,
		row: data
	});
	
	if (selectedIndex == undefined) {
		editIndex = $('#tcDetailTable').datagrid('getRows').length - 1;
	} else {
		editIndex = selectedIndex; // add row after the currently selected row
	}
	
	//$('#tcDetailTable').datagrid('beginEdit', editIndex);
}

/**
 * Find the index of field
 * @param target
 * @param field
 * @return index number if found. otherwise, return -1
 */
function getColIndex(target, field) {
	var index = -1;
	var columns = $(target).datagrid('options').columns;
	
	$.each(columns, function(i, v) {
		$.each(v, function(j, column) {
			//alert(j + ' ' + column['field'] + ' ' + field);
			if (column['field'] == field) {
				index = j;
			}
		});
	});
	
	return index;
}

function updateChangeOfColumns(target) {
	
	var data = {"total":0, "rows":[]};
	var row = {};
	var columns = $(target).datagrid('options').columns;
	var fields;
	
	if (columns.length > 0) {
		fields = columns[0];
	} else {
		fields = columns;
	}
	
	var content = $(target).datagrid('getData');
	
	// re-populate the data of the table
	$.each(content.rows, function(i, value) {
		$.each(fields, function(j, column) {
			var field = column['field'];

			// for the newly added column, data should be empty
			if (value[field] == undefined) {
				row[field] = '';
			} else {
				row[field] = value[field];
			}
			
			//alert(row.toSource());
		});
		
		data.rows.push(row);
		data.total++;
		row = {};
	});
	
	$(target).datagrid('loadData', data);
}

function removeColumn() {
	var columns = $('#tcDetailTable').datagrid('options').columns;
	var index = getColIndex('#tcDetailTable', curField);
	var fields;
	
	if (columns.length > 0 && index > -1) {
		fields = columns[0];
		fields.splice(index, 1);
		
		// update column to the table
		$('#tcDetailTable').datagrid({
			columns:[fields]
		});
		
		// update data according to the change of column
		updateChangeOfColumns('#tcDetailTable'); 
	}
}

function addColumn() {
	
	$.messager.prompt('TestDepot', 'Please enter header:', function(r) {
		if (r) {
			var col = r.replace(/ /g, '_');			
			var fields;
			var columns = $('#tcDetailTable').datagrid('options').columns;
			
			// for the first time, the array is not nested.
			if (columns.length > 0) {
				fields = columns[0];  // to parse nested array
			} else {
				fields = columns;  // to parse unnested array
			}
			
			//alert(fields.toSource());
			
			//var size = fields.length;
			var index = getColIndex('#tcDetailTable', curField);
				
			if (index > 0) {
				fields.splice(index, 0, {field:col, title:col, width:columnWidth, editor:'textarea'});
			} else {
				fields.push({field:col, title:col, width:columnWidth, editor:'textarea'});
			}
			
			// update column to the table
			$('#tcDetailTable').datagrid({
				columns:[fields]
			});
			
			// update data according to the change of column
			updateChangeOfColumns('#tcDetailTable'); 
		}
	});
}

function getSelectedRowIndex(target) {
	var selectedRow = $(target).datagrid('getSelected');
	
	if (selectedRow) {
		return $(target).datagrid('getRowIndex', selectedRow);
	} else {
		return undefined;
	}
}

function removeRow() {
	var selectedIndex = getSelectedRowIndex($('#tcDetailTable'));
	
	$('#tcDetailTable').datagrid('deleteRow', selectedIndex);
	
	// keep current row selected
	$('#tcDetailTable').datagrid('selectRow', selectedIndex);
}

function editRow(index) {
	
	if (editIndex != index) {
		if (endEditing()) {
			$('#tcExecDatagrid').datagrid('selectRow', index).datagrid('beginEdit', index);
			editIndex = index;
		} else {
			$('#tcExecDatagrid').datagrid('selectRow', editIndex);
		}
	}
}

function endEditing() {
	if (editIndex == undefined) {
		return true;
	}

	if ($('#tcExecDatagrid').datagrid('validateRow', editIndex)) {
		/*var ed = $('#tcExecDatagrid').datagrid('getEditor', {index:editIndex, field:'testResult'});
		var result = $(ed.target).combobox('getText');
		
		// update test result
		$('#tcExecDatagrid').datagrid('getRows')[editIndex]['testResult'] = result;
		$('#tcExecDatagrid').datagrid('getRows')[editIndex]['lastUpdUser'] = "KIM, JUNG SOO";*/
		
		$('#tcExecDatagrid').datagrid('endEdit', editIndex);
		editIndex = undefined;
		return true;
	} else {
		return false;
	}
}

/*
 * update temp test execution json file
 * this is useful on pagination
 */
function updateTestResults(rowIndex, changes) {
	
	//alert(rowIndex + '\n' + changes.toSource());
	
	// update last update user
	var now = new Date();
	
	// update row
	$('#tcExecDatagrid').datagrid('updateRow',{
		index:rowIndex,
		row:{
			lastUpdUser:user_last_name + ', ' + user_first_name.replace('-', '.') + ' (' + username.toUpperCase() + ')',
			lastUpdDate:now.toISOString()
		}
	});
	
	var updateResultJSON = JSON.stringify($('#tcExecDatagrid').datagrid('getRows')[rowIndex]);
	$.post('src/execution/update_result.php',{user_name:username,rowIndex:rowIndex, jstring:updateResultJSON},function(result){
		// after update refresh the datagrid to show the updates
		//$("#tcExecDatagrid").datagrid('reload'); 
	});
	
	/*
	$("#tcExecDatagrid").datagrid('endEdit', editIndex);
	var updatedRows = $("#tcExecDatagrid").datagrid('getChanges');
	
	if (updatedRows.length > 0) {
		// update last update user
		$.ajaxSetup({cache: false});
		$.get('src/common/getsession.php', function(user) {
			var now = new Date();
			
			for (var i = 0; i < updatedRows.length; i++) {
				var index = $("#tcExecDatagrid").datagrid('getRowIndex', updatedRows[i]);
				
				$("#tcExecDatagrid").datagrid('getRows')[index]['lastUpdUser'] = user;
				$("#tcExecDatagrid").datagrid('getRows')[index]['lastUpdDate'] = now.toISOString();
				
				// update row
				$('#tcExecDatagrid').datagrid('updateRow',{
					index:index,
					row:{
						lastUpdUser:user,
						lastUpdDate:now.toISOString()
					}
				});
			}
			
			var updateResultJSON = JSON.stringify(updatedRows);
			
			// commit changes
			$("#tcExecDatagrid").datagrid('acceptChanges');

			$.post('src/execution/update_result.php',{user_name:username,jstring:updateResultJSON},function(result){
				// after update refresh the datagrid to show the updates
				//$("#tcExecDatagrid").datagrid('reload');
			});
		});
	}*/
}

function saveTestResults() {
	$('#tcExecDatagrid').datagrid('endEdit', editIndex);
	setTimeout("do_saveTestResults()", 90);
	
}

function do_saveTestResults(){

        $.messager.progress({text:'Saving to TestCentral...'});
        $.post('src/execution/upload_result.php',{user_name:username},function(result){
                $.messager.progress('close');

                if (result != 1) {
                        alert('Failed in saving results: ' + result);
                } else {
                        // clear data
                        $('#tpNameExec').html('');
                        $('#tcExecDatagrid').datagrid('loadData', []);
                }
        });


}


/*
 * Load test cases from TestCentral and 
 * save it to temp cache
 */
function loadTestCases(node) {
	var cycle = $.trim(node.id.replace("cycle^", ""));

	/*var testResultJson = [{
							 "result":'P',
							 "text":'P'
						 },{
							 "result":'F',
							 "text":'F'
						 },{
							 "result":'B',
							 "text":'B'
						 },{
							 "result":'I',
							 "text":'I'
						 }];
	*/
	
	$.messager.progress({text:'Talking to TestCentral...'});
	
	$.post('src/runlist/getTestCasesByCyclePlan.php?user_name=' + username, {cycleplan:cycle}, function(result) {
		// display cycle plan name
		/*$('#layoutExec').layout('panel', 'center').panel({
			title:cycle + '&nbsp;&nbsp;&nbsp;&nbsp;' +
			'&nbsp;&nbsp;<img id=save style="vertical-align:middle;margin-bottom:2px" '+
			'src=img/content_save.png onclick="javascript:saveTestResults()" />'
		});*/
		
		/*
		var jsonArr = JSON.parse(result);
		var table = {"total":0, "rows":[]};
		var headers = [];
				
		table.total = jsonArr.length;
		table.rows = jsonArr;
		
		// create column headers
		for(var key in table.rows[0]) {
			var field = key.replace(/ /g, "_");
			
			switch (key) {
			case "priority":
				headers.push({field:field, title:key, width:50});
				break;
			case "lastUpdUser":
				headers.push({field:field, title:key, width:200});
				break;
			case "lastUpdDate":
				headers.push({field:field, title:key, width:200});
				break;
			case "executionMethod":
				headers.push({field:field, title:key, width:110});
				break;
			case "defectReportId":
				headers.push({field:field, title:key, width:150, 
				editor:{type:'text'}});
				break;
			case "blockedReason":
				headers.push({field:field, title:key, width:300, 
				editor:{type:'text'}});
				break;
			case "comments":
				headers.push({field:field, title:key, width:300, 
				editor:{type:'text'}});
				break;
			case "testResult":
				headers.push({field:field, title:key, width:70, styler:cellStyler, 
				editor:{type:'combobox', options:{data:testResultJson, valueField:'result', textField:'text'}}});
				break;
			default:
				headers.push({field:field, title:key, width:columnWidth});
				break;
			}
		}*/
		
		$.messager.progress('close');
		
		if (result != 1) {
			alert("Reading data from TestCentral is failed.");
		} else {
			$('#tpNameExec').html(cycle);
			
			// load newly created json file
			$.post('src/runlist/readTestPlanCases.php?user_name=' + username, function(result) {
				var data = JSON.parse(result);
				
				$('#tcExecDatagrid').datagrid('loadData', data);
			});
		}
		
		/*
		$('#tcExecDatagrid').datagrid({
			columns:[headers],
			data:table.rows
		});*/
	});
}

function cellStyler(value,row,index) {

	switch (value) {
		case 'B':
			return 'background-color:yellow;';
			break;
		case 'P':
			return 'background-color:green;color:white;';
			break;
		case 'F':
			return 'background-color:red;color:white;';
			break;
		case 'I':
			return 'background-color:blue;color:white;';
			break;
	}
}

function showTestDetail2(testcase) {
	
	// get test case detail
	$.messager.progress({text:'Loading test case detail...'});
	
	$.post('src/testscript/gettestcasesdetails.php?test_case=' + testcase, function(result) {
		$.messager.progress('close');
		
		var cols = [];
		var rows = [];
		var cells = [];
		var tcData = JSON.parse(result);
		var tcContent;
		var cell;
		
		tcContent = '<div style="color:#555;font-size:18px;font-weight:bold;">' + 
					tcData.TestCaseName + ': ' + tcData.CaseDescription + '</div>' +
					'<table bgcolor=#e2e2e2 width=100% height=auto><tr>';
		
		if (!(tcData.table instanceof Array)) {
			for (var key in tcData.table) {
				tcContent = tcContent + '<th valign=top>' + key + '</th>'; 
				cols.push(key);
				rows = rows.concat(tcData.table[key].split('\\r\\n'));
			}
			
			tcContent = tcContent + '</tr>';
			
			var size = rows.length / cols.length;
			
			for (var i = 0; i < size; i++) {
				
				var index = i;
				
				tcContent = tcContent + '<tr>';
				
				for (var c = 0; c < cols.length; c++) {
					cell = rows[index];
					
					if (cell.length == 0) {
						cell = '&nbsp;'
					} else {
						cell = cell.replace(/\n/g, '<br>');
					}
					
					tcContent = tcContent + '<td valign=top bgcolor=white>' + cell + '</td>';
					index += size;
				}				
				
				tcContent = tcContent + '</tr>';
			}
		}
		
		$('#layoutExec_south').html(tcContent);
	});
}

function showTestDetail(title, description, scriptPath, gitUrl) {
	$('#testcasetable').show();
	$('#description').show();
	$('#scriptDiv').show();
	$('#content').hide();
	
	// get test case detail
	var xmlHttp = getXMLHttp1();
	xmlHttp.open("GET", "src/testscript/gettestcasesdetails.php?test_case=" + title, false);
	xmlHttp.send(null);
	
	//alert(xmlHttp.responseText);
	var tcData = JSON.parse(xmlHttp.responseText);
	
	// display test case details
	/*$('#layoutScript').layout('panel', 'center').panel({
		title:tcData.TestCaseName + '&nbsp;&nbsp;&nbsp;&nbsp;' +
		'&nbsp;&nbsp;<img id=save style="vertical-align:middle;margin-bottom:2px" src=img/content_save.png onclick="javascript:saveTestCase()" />' +
		'&nbsp;&nbsp;<img id=addRw style="vertical-align:middle;margin-bottom:2px" src=img/add_row.png onclick="javascript:addRow()" />' +
		'&nbsp;&nbsp;<img id=removeRw style="vertical-align:middle;margin-bottom:2px" src=img/delete_row.png onclick="javascript:removeRow()" />' +
		'&nbsp;&nbsp;<img id=addCol style="vertical-align:middle;margin-bottom:2px" src=img/add_column.png onclick="javascript:addColumn()" />' +
		'&nbsp;&nbsp;<img id=removeCol style="vertical-align:middle;margin-bottom:2px" src=img/delete_column.png onclick="javascript:removeColumn()" />'
	});*/
	$('#tcNameDev').html(title);
	$('#description').html(description);
	$('#savelbtn').unbind('click');  // clear onclick event
	$('#savelbtn').click(function() {saveTestCase();}); //set onclick event function
	
	// if there is no table, then it will return empty array
	var cols = [];
	var rows = [];
	var headers = [];
	var row = {};
	var tableData = {"total":0, "rows":[]};

	if (!(tcData.table instanceof Array)) {
		// get test case table columns
		
		for (var key in tcData.table) {
			// get headers
			headers.push({field:key, title:key, width:columnWidth, editor:'textarea'});
			
			cols.push(key);
			rows = rows.concat(tcData.table[key].split('\\r\\n'));
		}
		
		var size = rows.length / cols.length;
		
		for (var i = 0; i < size; i++) {
			
			var index = i;
			
			for (var c = 0; c < cols.length; c++) {
				row[cols[c]] = rows[index].replace(/\n/g, '<br>');
				index += size;
			}
			
			//alert(row.toSource());
			tableData.rows.push(row);
			tableData.total++;
			row = {};
		}
	} 
	
	// create column to the table
	$('#tcDetailTable').datagrid({
		columns:[headers],
		data:tableData.rows
	});
	
	// parse script path
	//scriptPath = scriptPath.replace(/\\r\\n/g, '');
	$('#scriptLoc').text(scriptPath);
	$('#cbGitSelect').combobox('select', gitUrl);
	
	// load table data
	//$('#tcDetailTable').datagrid('loadData', tableData);
}

function addNewTC(node) {
	// get suite and group name
	var suite = node.id.replace(/suite\^/, '');
	//var parent = $('#treeTestCase').tree('getParent', node.target);
	//var group = $('#treeTestCase').tree('getParent', parent.target);
	
	// if it shows only suite name, we don't execute this function.
	// it is on create new TC mode.
	if (suite.length > 0) {
		$('#testcasetable').show();
		$('#description').show();
		$('#content').hide();
		$('#scriptDiv').show();
		
		// update with suite name so that it declares new test case when calling update test case API.
		/*$('#layoutScript').layout('panel', 'center').panel({
			title:suite + '&nbsp;&nbsp;&nbsp;&nbsp;' +
			'&nbsp;&nbsp;<img id=save style="vertical-align:middle;margin-bottom:2px" src=img/content_save.png onclick="javascript:saveTestCase()" />' +
			'&nbsp;&nbsp;<img id=addRw style="vertical-align:middle;margin-bottom:2px" src=img/add_row.png onclick="javascript:addRow()" />' +
			'&nbsp;&nbsp;<img id=removeRw style="vertical-align:middle;margin-bottom:2px" src=img/delete_row.png onclick="javascript:removeRow()" />' +
			'&nbsp;&nbsp;<img id=addCol style="vertical-align:middle;margin-bottom:2px" src=img/add_column.png onclick="javascript:addColumn()" />' +
			'&nbsp;&nbsp;<img id=removeCol style="vertical-align:middle;margin-bottom:2px" src=img/delete_column.png onclick="javascript:removeColumn()" />'
		});*/
		
		$('#tcNameDev').html(suite);
		$('#savelbtn').unbind('click');  // clear onclick event
		$('#savelbtn').click(function() {saveTestCase();}); //set onclick event function
		
		// show table buttons
		//$('#colAddlbtn').show();
		//$('#colDellbtn').show();
		$('#rowAddlbtn').show();
		$('#rowDellbtn').show();
		
		var TPSHeaderValues = $.trim(node.attributes);
		
		if (TPSHeaderValues.length > 0) {
			var tableData = {"total":0, "rows":[]};
			var headers = [];
			var values = TPSHeaderValues.split(',');
			
			for (index in values) {
				val_title = $.trim(values[index]);
				val_field = val_title;
				
				// create column headers
				if (val_title.length > 0 && val_field.length > 0) {
					headers.push({field:val_field, title:val_title, width:columnWidth, editor:'textarea'});
				}
			}
			
			$('#tcDetailTable').datagrid({
				columns:[headers]
			});
			
			$('#tcDetailTable').datagrid('loadData', tableData); // make the table empty
			$('#description').text('Enter description here');
			$('#scriptLoc').text('');
			
			// for script path
			$('#scriptDiv').show();
		} else {
			alert("No column headers are defined in TestSuite.");
			
			// load test suite
			$('#treeTestCase').tree('select', node.target);
		}
	}
}

function addNewSuite(node) {
	//var parent = $('#treeTestCase').tree('getParent', node.target);
	//var groupName = parent.text;
	//var fa = node.text;
	//var fa = node.id.replace(/fa\^/, '');

	//if (groupName.length > 0 && fa.length > 0) {
	
	var ids = node.id.split(/\|/);
	var groupName = ids[0].replace(/group\^/, '');
	var folder = ids[1].replace(/folder\^/, '');
	
	if (folder.length > 0 && groupName.length > 0) {
		$('#testcasetable').hide();
		$('#description').hide();
		$('#content').show();
		
		/*$('#layoutScript').layout('panel', 'center').panel({
			title:fa + '&nbsp;&nbsp;&nbsp;&nbsp;' +
			'&nbsp;&nbsp;<img id=new style="vertical-align:middle;margin-bottom:2px" src=img/content_save.png onclick="javascript:saveTestSuite()" />'
		});*/
		//$('#tcNameDev').html(fa);
		$('#tcNameDev').html(folder);
		$('#savelbtn').unbind('click');  // clear onclick event
		$('#savelbtn').click(function() {saveTestSuite();}); //set onclick event function
		
		var groupNameArr = groupName.split(" ");
		var orgName = groupNameArr[0];
		var phaseName = groupNameArr[1];
		
		var userinfo = user_last_name + ', ' + user_first_name.replace('-', '.') + ' (' + username + ')';
		var jsonTestSuiteTemp = {
				'Table':[
					         {'TestSuiteId':''},
					         {'TestSuiteName': folder},
					         {'SuiteUserGivenName': 'Enter given name here'},
					         {'TestSuiteOrderId':''},
					         {'GroupName':groupName},
					         {'GroupId':''},
					         {'ReadPermission':''},
					         {'WritePermission':''},
					         {'OrgName':orgName},
					         {'OrgId':''},
					         {'PhaseName':phaseName},
					         {'PhaseId':''},
					         {'LastUpdUser':userinfo},
					         {'LastUpdUserId':''},
					         {'LastUpdDate':''},
					         {'StatusId':''},
					         {'StatusDescription':'Verified'},
					         {'FunctionalAreaName':folder},
					         {'FunctionalAreaId':''},
					         {'TPSHeaderValue':'&nbsp;'},
					         {'UserId':''},
					         {'login':''},
					         {'LoginDetail':userinfo},
					         {'ReadPermissionDesc':'Motorola Only'},
					         {'WritePermissionDesc':'Group Only'}
			    ],
			    'Table1':[
					         {'TestSuiteName':''},
					         {'TestSuiteId':''},
					         {'Abstract':''},
					         {'Procedures':''},
					         {'Notes':''},
					         {'DocumentPath':'tc.mot.com'}
			    ]
		};
					
		populateTestSuiteJSON(node, JSON.stringify(jsonTestSuiteTemp), 1);
		updateSaver();
	}
}

function padNum(number) {
	return ("00" + number).slice(-3);
}

function updateSaver() {
	// change following information before saving
	/*$.ajaxSetup({cache: false});
	$.get('src/common/getsession.php', function(data) {
		$('#LastUpdUser').text(data);
		$('#LoginDetail').text(data);
		
		var reg = /\(.*\)/;
		var user = reg.exec(data);
		$('#login').text(user[0].replace('(', '').replace(')', ''));
	});*/
	
	$('#login').text(username);
}

function saveTestSuite() {
	/*var suite = $('#layoutScript').layout('panel', 'center').panel('options').title;
	suite = suite.substr(0, suite.indexOf("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"));*/
	var suite = $('#tcNameDev').html();
	
	var content = {Table:{}, Table1:{}};
	var id = '';
	
	$("#table").children().each(function() {
		id = $(this).attr('id');
		
		if (id != undefined) {
			//alert($(this).attr('id') + ":" + $(this).text());
			content.Table[id] = $(this).text();
		}
	});
	
	$("#table1").children().each(function() {
		id = $(this).attr('id');
		
		if (id != undefined) {
			content.Table1[id] = $(this).val();
		}
	});
	
	var suiteContent = JSON.stringify(content);
	
	// save TestCentral
	$.messager.progress({text:'Saving...'});
	
	$.post('src/testscript/update_testsuite.php', {suite_name:suite, suite_info:suiteContent}, function (result) {
		
		$.messager.progress('close');
		if (result == 3) {
			alert('Test Suite saved successfully');
			
			// automatically refresh the parent node to reflect the changes of test suite
			var selNode = $('#treeTestCase').tree('getSelected');
			var parent = $('#treeTestCase').tree('getParent', selNode.target);
			$('#treeTestCase').tree('reload', parent.target);
		} else {
			alert('Test Suite is not saved successfully\n' + result);
		}
	});
	
	/*var xmlHttp = getXMLHttp1();
	xmlHttp.open("GET", "src/testscript/update_testsuite.php?suite_name=" + suite + "&suite_info=" + JSON.stringify(content), false);
	xmlHttp.send(null);
	
	alert('Saved');*/
}

function populateTestSuiteJSON(node, jsonString, flag) {
	
	var suiteData = JSON.parse(jsonString);
		
	// display the suite details
	if (suiteData.hasOwnProperty('Table') && suiteData.hasOwnProperty('Table1')) {
		var table = '';
		var table1 = '';
				
		$('#savelbtn').unbind('click');  // clear onclick event
		$('#savelbtn').click(function() {saveTestSuite();});  // set function on click event
		
		// populate Table
		for (var index in suiteData.Table) {
			for (var key in suiteData.Table[index]) {
				switch (key) {
				case 'TestSuiteId':
					break;
				case 'TestSuiteName':
					$('#tcNameDev').html(suiteData.Table[index][key]);
					break;
				case 'TestSuiteOrderId':
					break;
				case 'TestCaseOrderId':
					break;
				case 'GroupId':
					break;
				case 'ReadPermission':
					break;
				case 'WritePermission':
					break;
				case 'OrgId':
					break;
				case 'PhaseId':
					break;
				case 'LastUpdUserId':
					break;
				case 'StatusId':
					break;
				case 'AutomationStatusId':
					break;
				case 'FunctionalAreaId':
					break;
				case 'UserId':
					break;
				case 'login':
					break;
				case 'SuiteUserGivenName':
					table += '<div class=label>' + key + '</div><div id=' + key + 
							 ' style="height:14px;border-bottom:solid 1px #B1B2B3;' +
							 'font-family:Arial,Helvetica,Sans-Serif;font-size:12px" ' +
							 'onclick="javascript:setEdit(this)" onBlur="javascript:setReadOnly(this)">' + suiteData.Table[index][key] + 
							 '</div>';
					break;
				case 'TPSHeaderValue':
					/*table += '<div class=label>' + key + '</div><div id=' + key + 
							 ' style="height:14px;border-bottom:solid 1px #B1B2B3;' +
							 'font-family:Arial,Helvetica,Sans-Serif;font-size:12px" ' +
							 'onclick="javascript:setEdit(this)" onBlur="javascript:setReadOnly(this)">' + suiteData.Table[index][key] + 
							 '</div>';*/
					var TPSHeaderValue = suiteData.Table[index][key];
					
					if (TPSHeaderValue == '&nbsp;' || TPSHeaderValue == '') {
						// create default column headings
						TPSHeaderValue = 'Step Description, Expected Results';
					} else {
						// for existing test suite, disable save button
						// do not allow saving - it will rip off headings webAPI problem
						$('#savelbtn').linkbutton('disable');
					}
					
					table += '<div class=label>' + key + '</div><div id=' + key + '>' + TPSHeaderValue + '</div>';
					
					// save TPSHeaderValue to the node's attribute
					$('#treeTestCase').tree('update', {
						target:node.target,
						attributes:TPSHeaderValue
					});
					break;
				default:
					table += '<div class=label>' + key + '</div><div id=' + key + '>' + suiteData.Table[index][key] + '</div>';
					break;
				}
			}
		}
		
		// populate Table1
		for (var index in suiteData.Table1) {
			for (var key in suiteData.Table1[index]) {
				switch (key) {
				case 'TestSuiteName':
					break;
				case 'TestSuiteId':
					break;
				default:
					table1 += '<div class=label>' + key + '</div>' +
					          '<textarea id=' + key + ' style="width:100%;height:100px;border:solid 1px #B1B2B3;' +
					          'font-family:Arial,Helvetica,Sans-Serif;font-size:12px" ' +
					          'onclick="javascript:setEdit(this)" onBlur="javascript:setReadOnly(this)">' + 
					          suiteData.Table1[index][key] + '</textarea>';
					break;
				}
			}
		}
		
		$('#table').html(table);
		$('#table1').html(table1);
	} else {
		alert("Test suite is corrupted. Cannot read it from TestCentral!");
	}
}

function showTestSuite(node) {
	$('#testcasetable').hide();
	$('#description').hide();
	$('#scriptDiv').hide();
	$('#content div').empty(); // make sure to clear the content before loading test suite
	$('#content').show();
	
	var suiteName = node.id.replace("suite^", "");
	var xmlHttp = getXMLHttp1();
	xmlHttp.open("GET", "src/testscript/getTestSuiteInfo.php?suite=" + suiteName, false);
	xmlHttp.send(null);
	
	//alert(xmlHttp.responseText);
	populateTestSuiteJSON(node, xmlHttp.responseText, 2);
}

function findKey(match, array) {
	for(var i = 0; i < array.length; i++) {
		if (array[i] == match) {
			return true;
		}
	}
	
	return false;
}

function resizePanelBody(target) {
	var pBodyHeight = $(target).panel('body').height();
	var divH_desc = $('#description').outerHeight();
	var divH_script = $('#scriptDiv').outerHeight();
	var divH_source = pBodyHeight - divH_desc - divH_script;
	
	$('#testcasetable').height(divH_source - 2); // consider border top 2px
	
	// set width and height of the datagrid so that it fits to its parent DOM (#testcasetable)
	$('#tcDetailTable').datagrid({
		width: 300,
		height: 100
	});
	
	//alert("body height=" + pBodyHeight + '\n' + "desc height=" + divH_desc + '\n' + "target height=" + divH_source +
	//		"\ndatagrid height=" + $('#tcDetailTable').datagrid('options').height);
}

/*
 * addFunction(fname)
 * Jung Soo Kim
 * March-21-2012
 * Add cloned function to the execution queue
function addFunction(source){
	var fstring = '';
	var list = [];
	var device = '';
	
	if ($(source).find('element.description:eq(0)').html()) {
		fstring = 'class=' + $(source).find('p.class:eq(0)').text() + '<br>' + // class name
				  'method=' + $(source).find('p.function:eq(0)').text() + '<br>' + // method name
				  'delay=' + $(source).find('element.delay:eq(0)').text() + '<br>' + // delay
				  '<p hidden class="Description">' + $(source).find('element.description:eq(0)').html().replace(/<br>/gi, '\n') + '</p><br>'; // description

		// parameters
		for (var i = 0; i < $(source).find('p.ParameterName').length; i++) {
			list.push('<p hidden class="ParameterGroup">' + $(source).find('p.ParameterGroup:eq(' + i + ')').text() + '</p>' + 
					  $(source).find('p.ParameterName:eq(' + i + ')').text() + '=' + 
					  $(source).find('element.ParameterValue:eq(' + i + ')').text() +
					  '<p hidden class="ParameterOption">' + $(source).find('element.ParameterOption:eq(' + i + ')').text() + '</p>');
		}
		
		fstring = fstring + list.join('<br>');
	}
	
	// update the cell
	var oldData = data.rows[scriptQueueSelectedRowIndex];
	
	switch (scriptQueueSelectedField) {
	case 'target':
		oldData.target = fstring;
		break;
	case 'companion':
		oldData.companion = fstring;
		break;
	}
	
	$("#scriptQueue").datagrid('loadData', data);
}*/

/*
 * convertTimeSpinnerFormat
 * it converts time value to timespinner format
 * Jung Soo Kim
 */
function convertTimeSpinnerFormat(value){
	value = value.replace('h', ':');
	value = value.replace('m', ':');
	value = value.replace('s', '');
	
	return value;
}

/*
 * convertScriptFormat
 * it converts time value to script format
 * Jung Soo Kim
 * April 6, 12
 */
function convertScriptFormat(value){
	value = value.split(':');
	value = value[0] + 'h' + value[1] + 'm' + value[2] + 's';
	
	return value;
}




/*
 * By Jing-May
 *
*/

function sleep(milliseconds) {
  var start = new Date().getTime();
  for (var i = 0; i < 1e7; i++) {
    if ((new Date().getTime() - start) > milliseconds){
      break;
    }
  }
}

/*
 * Search Test Suite function
*/
function search(treeName, searchTreeName, sbName, value) {
	
	if (value.length > 0) {
		if ($(sbName).searchbox('options').prompt == 'Search test suite') {
			$.messager.progress({text:'Searching test suite...'});
			$.post('src/testscript/searchTestSuites.php', {username:username, search:value}, function(result) {
				$.messager.progress('close');
		
				$(treeName).hide();
				$(searchTreeName).tree({
					data:JSON.parse(result)
				});
				$(searchTreeName).show();
			});
		} else {
			$.messager.progress({text:'Searching test plan...'});
			$.post('src/testscript/searchTestPlan.php', {username:username, search:value}, function(result) {
				$.messager.progress('close');
		
				$(treeName).hide();
				$(searchTreeName).tree({
					data:JSON.parse(result)
				});
				$(searchTreeName).show();
			});
		}
	} else {
		alert('Please enter search word.');
	}
}


/******************************************************************
 * treeTestCase and searchTreeTestCase common functions
 */
function onSelectHandler(treeName, node) {
	if ($(treeName).tree('isLeaf', node.target)) {
		// open test case
		$('#savelbtn').show();
		$('#savelbtn').linkbutton('enable');
		$('#uploadlbtn').hide();
		$('#colAddlbtn').show();
		$('#colDellbtn').show();
		$('#rowAddlbtn').show();
		$('#rowDellbtn').show();
		
		showTestDetail(node.text, node.attributes.description, node.attributes.scriptPath, node.attributes.gitUrl);
		
		// resize the layout center panel so that testcase datagrid fits
		$('#layoutScript').layout('resize');
	} else if ((/^suite\^/).test(node.id)) {
		$('#savelbtn').show();
		$('#savelbtn').linkbutton('enable');
		$('#uploadlbtn').show();
		$('#colAddlbtn').hide();
		$('#colDellbtn').hide();
		$('#rowAddlbtn').hide();
		$('#rowDellbtn').hide();
		
		showTestSuite(node);
	} else {
		// anything else hide them all
		$('#savelbtn').hide();
		$('#uploadlbtn').hide();
		$('#colAddlbtn').hide();
		$('#colDellbtn').hide();
		$('#rowAddlbtn').hide();
		$('#rowDellbtn').hide();
		
		$('#tcNameDev').html(node.text);
	}
}

function onContextMenuHandler(e, treeName, node) {
	e.preventDefault();
	
	var selectedNode = $(treeName).tree('getSelected');

	// show context menu if node is not test case
	if (node.id != '2' && selectedNode && selectedNode.id == node.id) {
		var children = $(treeName).tree('getChildren', node.target);
		var pattern = /(^suite\^|^master\^|^cycle\^)/;
		
		if ((pattern).test(node.id)) {
			$('#treeTestCaseMenu').menu('enableItem', $('#m-refresh')[0]);
		} else {
			$('#treeTestCaseMenu').menu('disableItem', $('#m-refresh')[0]);
		}
									
		if ((children.length > 0 && (/^suite\^/).test(children[0].id)) || (/^suite\^/).test(node.id)) {
			$('#treeTestCaseMenu').menu('enableItem', $('#m-new')[0]);
			$('#treeTestCaseMenu').menu('enableItem', $('#m-download')[0]);
		} else {
			$('#treeTestCaseMenu').menu('disableItem', $('#m-new')[0]);
			$('#treeTestCaseMenu').menu('disableItem', $('#m-download')[0]);
		}
		
		if ((/^suite\^/).test(node.id)) {
			$('#treeTestCaseMenu').menu('enableItem', $('#m-download')[0]);
			$('#treeTestCaseMenu').menu('enableItem', $('#m-upload')[0]);
		} else {
			$('#treeTestCaseMenu').menu('disableItem', $('#m-download')[0]);
			$('#treeTestCaseMenu').menu('disableItem', $('#m-upload')[0]);
		}
		
		// enable only reload menu item for master or cycle
		if ((/^master\^/).test(node.id) || (/^cycle\^/).test(node.id)) {
			$('#treeTestCaseMenu').menu('disableItem', $('#m-new')[0]);
			$('#treeTestCaseMenu').menu('disableItem', $('#m-download')[0]);
		}

		$('#treeTestCaseMenu').menu({
			onClick:function(item) {
				switch (item.name) {
					case 'new':
						if ((/^suite\^/).test(node.id)) {
							// add new test case
							var title = $('#layoutScript').layout('panel', 'center').panel('options').title;
							
							if (title != node.id.replace('suite^', '') + " - corrupted") {
								// show save and upload button
								$('#savelbtn').show();
								$('#savelbtn').linkbutton('enable');
								$('#uploadlbtn').hide();
								$('#colAddlbtn').show();
								$('#colDellbtn').show();
								$('#rowAddlbtn').show();
								$('#rowDellbtn').show();
								addNewTC(node);
							}
						} else {
							// add new suite
							// show buttons for test case
							$('#savelbtn').show();
							$('#savelbtn').linkbutton('enable');
							$('#uploadlbtn').show();
							$('#colAddlbtn').hide();
							$('#colDellbtn').hide();
							$('#rowAddlbtn').hide();
							$('#rowDellbtn').hide();
							addNewSuite(node);
						}
						break;
					case 'refresh':
						$('#treeTestCase').tree('select', node.target);
						$('#treeTestCase').tree('reload', node.target);
						break;
					case 'download':
						//alert(node.id);
						var suite_str = node.id;
						suite_str = suite_str.replace('suite^','');
						alert(suite_str+" will be saved to a csv file!");

						wind1 = window.open("src/testscript/download_excel.php?username="+username+"&suite="+node.id,"_blank", "top=500, left=500, width=400, height=400");
						wind1.document.write("<html><head><title>Test Depot</title></head><body><p> Please wait, check the buttom of this window.</p><p><strong>It may take minutes if the size of suite is big!</strong></p></body></html>");
						//window.close("wind1");

						break;
					case 'upload':
                        // Need more testing to release those code to avoid risk of damage of data in Test Central DB
                        // We can use test suite of Assist from 
                        // Emerging Communication Group->ECG_Andr.X_Fone.Smart_Actions.Assist2o, 
                        // because this test suite has more complicate structure of text in test cases.
                        //
                        //
                        //var suite_str = node.id;
                        //suite_str = suite_str.replace('suite^','');
                        //alert(suite_str+" will be uploaded with a csv file!");
                        //uploadwindow(suite_str);
                        //
                        alert("Not ready to release this feature! Need more testing!");
                        break;

				}
			}
		}).menu('show', {
			left:e.pageX,
			top:e.pageY
		});
	}
}

/**
 * searchbox function
 * Jung soo Kim
 * @return
 */
function showSuiteTree(sbName, treeNameReg, treeNameSearch) {
	$(sbName).searchbox({
		'prompt':'Search test suite',
		'value':''
	});
	$(treeNameReg).tree({url:'src/testscript/testscripttreeBySuite.php?user_name='+username});
	$(treeNameSearch).tree({url:'src/testscript/searchTestSuites.php?user_name='+username});
	$(treeNameReg).show();
	$(treeNameSearch).hide();
}

function showPlanTree(sbName, treeNameReg, treeNameSearch) {
	$(sbName).searchbox({
		'prompt':'Search test plan',
		'value':''
	});
	$(treeNameReg).tree({url:'src/testscript/testscripttreeByPlan.php?user_name='+username});
	$(treeNameSearch).tree({url:'src/testscript/searchTestPlan.php?user_name='+username});
	$(treeNameReg).show();
	$(treeNameSearch).hide();
}

/**
 * treeTestPlan function
 * @param node
 * @return
 */
function treeTestPlanOnClickHandler(node) {
	if (node.id.indexOf("cycle^") > -1) {
		// check the temp JSON file for the working test plan for updating results.
		$.ajaxSetup({cache: false});
		$.get('src/runlist/readTestPlanName.php?user_name=' + username, function(planname) {
			if (planname != '') {
				$.messager.confirm('TestDepot', "The test results are not submitted to TestCentral yet. " + 
					"Do you want to discard and load from TestCentral?", function(response) {
					if (response) {
						// if says yes, load new test cases.
						loadTestCases(node);
					}
				});
			} else {
				loadTestCases(node);
			}
		});
	}
}