/*
 * addPackage()
 * Jung Soo Kim
 */
function addPackage() {
	var e = document.getElementById('cc');       		// get framework selection element
	var framework = e.options[e.selectedIndex].value;   // get selected framework
	//var nSelected = $('#mtree').tree('getSelected');    // selected node


	$.messager.prompt('invader+', 'Enter your application package name? (For example: account, alarm, browser)', function(response) {
		if(response.indexOf("\.") != -1){
			alert(response + " :incorrect format!");
			exit;

		}
		if (response) {
			response = "com.moto.android.apython.app." + response;
			$.post('src/testscript/addPackage.php?framework=' + framework + '&package=' + response, function(result) {
				if (result != true) {
					alert(result);
				} else {
					refreshModuleTree(e);  // refresh test API tree on successfully adding package
				}
			});
		}
	});
}

/*
 * addMethod()
 * Jung Soo Kim
 */
function addMethod() {
	//var e = document.getElementById('cc');       	// get framework selection element
	var nPackage = $('#mtree').tree('getSelected'); // selected node
	
	if (nPackage) {		
		if ($('#mtree').tree('isLeaf', nPackage.target)) {  // if method node is selected, get its package node
			nPackage = $('#mtree').tree('getParent', nPackage.target);
		}
		
		$.messager.prompt('invader+', 'Enter new method name:', function(response) {
			if (response) {
				
				$.post('src/testscript/addMethod.php?idPackage=' + nPackage.id + '&method=' + response, function(result) {
					if (result != true) {
						alert(result);
					} else {
						$('#mtree').tree('reload', nPackage.target); // reload the node
					}
				});
			}
		});
	} else {
		alert('Select package under which new method will be added.');
	}
}






/*
 * window open
 * Jung Soo Kim
 */
function windowOpen(url) {
	//var w = 400;
	//var h = 200;
	//var left = (screen.width/2) - (w/2);
	//var top = (screen.height/2) - (h/2);
	//var targetWin = window.open(url, 'win_upload', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, addressbar=no, width='+w+', height='+h+', top='+top+', left='+left);
}



/*
 * TestAPIInfo UI resizing
 * Jung Soo
 */
function resizeTestAPIInfo() {
	var w = document.getElementById('frmTestAPI').offsetWidth;  // get width of the form
	var h = document.getElementById('frmTestAPI').offsetHeight; // get height of the form
	
	document.getElementById('taDescription').style.width = w + 'px';  // adjust the size of Description text area
	document.getElementById('taExample').style.width = w + 'px';  // adjust the size of Example text area
	document.getElementById('containerdgParameter').style.width = Math.round(w * 0.6) + 'px';  // adjust the size of parameter datagrid
	document.getElementById('containerdgParameter').style.height = (h - 355) + 'px';
	document.getElementById('containerdgOption').style.width = (w - Math.round(w * 0.6) - 5) + 'px';  // adjust the size of option datagrid
	document.getElementById('containerdgOption').style.height = (h - 355) + 'px';
}

/*
 * Edit button toggle function
 * Jung Soo
 */

/* Jing-May add some code to call check_scripts_need_modified.php and save_parameter_list.php and change_file_name.php */
function toggle(e) {
	var node = $('#mtree').tree('getSelected');  // edit button enabled only when test API is selected	
	
	if (node) {
		if ($('mtree').tree('isLeaf', node.target)) {
			(e.text == 'Edit') ? document.getElementById('taDescription').disabled = false : document.getElementById('taDescription').disabled = true;
			(e.text == 'Edit') ? document.getElementById('taExample').disabled = false : document.getElementById('taExample').disabled = true;
			(e.text == 'Edit') ? $('#btAddParameter').linkbutton('enable') : $('#btAddParameter').linkbutton('disable');
			(e.text == 'Edit') ? $('#btDeleteParameter').linkbutton('enable') : $('#btDeleteParameter').linkbutton('disable');
			(e.text == 'Edit') ? $('#btAddOption').linkbutton('enable') : $('#btAddOption').linkbutton('disable');
			(e.text == 'Edit') ? $('#btDeleteOption').linkbutton('enable') : $('#btDeleteOption').linkbutton('disable');
			(e.text == 'Edit') ? enableEditor($('#dgOption')) : disableEditor($('#dgOption'));
			(e.text == 'Edit') ? enableEditor($('#dgParameter')) : disableEditor($('#dgParameter'));

			var pkg_curr = $('#packageName').text();
        	var mtd_curr = $('#methodName').text();
        	
        	var node = $('#mtree').tree('getSelected');
        	var parent = $('#mtree').tree('getParent', node.target);
        	
        	if (e.text == 'Save') {
        		var parameters = $('#dgParameter').datagrid('getRows');

				$.post('src/testscript/check_scripts_need_modified.php',{username:username,pname:pkg_curr,mname:mtd_curr,parameters:parameters}, function(result3){
					// return result need to be used in confirm message
					if(result3){
						$.messager.confirm('The following test scripts are affected by this change:',result3,function(r){
							if (r) {
								// save the data
								$.post('src/testscript/updateMethod.php?id=' + $('#idMethod').text() + '&description=' + document.getElementById('taDescription').value.replace(/\n\r?/g, '\\n') + 
									   '&example=' + document.getElementById('taExample').value.replace(/\n\r?/g, '\\n'), {parameters:parameters}, function(result) {
										   if (!result) {
											   alert(result);
										   } else {
											   loadTestAPIInfo(document.getElementById('cc').value, parent.text, node.id, node.text);
										   }
								});

								// add affected word to file name of test script json file
								$.post('src/testscript/change_file_name.php', {rstring:result3}, function(result2){});
							} else {
								loadTestAPIInfo(document.getElementById('cc').value, parent.text, node.id, node.text); // revert to origin state
							}
						});
					} else {
						// save the data
						$.post('src/testscript/updateMethod.php?id=' + $('#idMethod').text() + '&description=' + document.getElementById('taDescription').value.replace(/\n\r?/g, '\\n') + 
							   '&example=' + document.getElementById('taExample').value.replace(/\n\r?/g, '\\n'), {parameters:parameters}, function(result) {
								   if (!result) {
									   alert(result);
								   } else {
									   loadTestAPIInfo(document.getElementById('cc').value, parent.text, node.id, node.text);
								   }
						});
					}
                });
        	} else {
        		var parameters = $('#dgParameter').datagrid('getRows');
                $.post('src/testscript/save_parameter_list.php',{username:username,pname:pkg_curr,mname:mtd_curr,parameters:parameters}, function(result){},'json');
        	}
			
        	(e.text == 'Edit') ? $('#bEdit').linkbutton({text:'Save', iconCls:'icon-cog-save'}) : $('#bEdit').linkbutton({text:'Edit', iconCls:'icon-cog-edit'});
		} else {
			alert('Please select test API to view or edit');
		}
	} else {
		alert('Please select test API to view or edit');
	}
}

function enableEditor(e) {
	var selected = e.datagrid('getSelected');
	
	if (selected) {
		var index = e.datagrid('getRowIndex', selected);
		e.datagrid('beginEdit', index);
	}
}

function disableEditor(e) {
	var selected = e.datagrid('getSelected');
	
	if (selected) {
		var index = e.datagrid('getRowIndex', selected);
		e.datagrid('endEdit', index);
	} 
}

/*
 * loadTestAPIInfo
 * Jung Soo Kim
 */
function loadTestAPIInfo(framework, strPackage, strID, strMethod){
	resizeTestAPIInfo();  // resize to fit the panel
	
	$('#dgParameter').datagrid({  // construct datagrid for parameter
		columns:[[{
			field:'name', title:'Parameter Name', width:50, editor:'text'
		},{
			field:'type', title:'Input Type', width:50,
			editor:{
				type:'combobox',
				options:{
					valueField:'id',
					textField:'name',
					data:[
					      {id:'text', name:'text'},
					      {id:'combobox', name:'combobox'},
					      {id:'numberspinner', name:'numberspinner'},
					      {id:'datetimebox', name:'datetimebox'},
					      {id:'timebox', name:'timebox'}
					     ],
					required:true,
					editable:false
				}
			}
		},{
			field:'options', hidden:true
		},{
			field:'id', hidden:true
		}]],
		toolbar:[{
			id:'btAddParameter',
			iconCls:'icon-cog-add',
			disabled:true,
			text:'Add Parameter',
			handler:function(){
				$('#dgParameter').datagrid('appendRow', {
					name:'',
					type:'',
					options:'',
					id:''
				});
				
				var lastIndex = $('#dgParameter').datagrid('getRows').length - 1;
				$('#dgParameter').datagrid('selectRow', lastIndex);
				$('#dgParameter').datagrid('beginEdit', lastIndex);
			}
		},{
			id:'btDeleteParameter',
			iconCls:'icon-cog-delete',
			text:'Delete Parameter',
			disabled:true,
			handler:function(){
				var selected = $('#dgParameter').datagrid('getSelected'); // get selected row
				
				if (selected) {
					var index = $('#dgParameter').datagrid('getRowIndex', selected); // get the index
					$('#dgParameter').datagrid('deleteRow', index);  // delete selected row
				}
				
				$('#dgOption').datagrid('loadData', []);  // clear the option datagrid
			}
		}],
		fitColumns:true,
		singleSelect:true,
		rownumbers:true,
		fit:true,
		border:0,
		nowrap:false,
		onSelect:function(index, rowData){
			//var options = [];  // array of option string
			var data = {"total":0, "rows":[]};  // data for option datagrid to load
			var update = getRowInEditMode($('#dgParameter'), 'name');  // row index to be updated
			
			if (update) {
				$(this).datagrid('endEdit', $(this).datagrid('getRowIndex', update));  // end the edit mode of the last row
				
				var options = [];   // array to contain option list
				var rows = $('#dgOption').datagrid('getRows'); // get rows of the data
				
				for (var i = 0; i < rows.length; i++) {
					$('#dgOption').datagrid('endEdit', i);  // make sure to end the edit
					options.push(rows[i].option);  // add options
				}
				
				update.options = options.join('|');  // update option field if the row is in edit mode
			}
			
			if ($('#bEdit').linkbutton('options').text == 'Save') {  // editor will be active only edit mode	
				$(this).datagrid('beginEdit', index);  // turn on edit box for the selected row 
				
				var ed = $(this).datagrid('getEditor', {index:index, field:'name'});
				$(ed.target).focus();  // focus the name field editor
			}

			if (rowData.options.length > 0) {
				options = rowData.options.split('|');
				
				for (var i = 0; i < options.length; i++) {
					data.rows.push({
						'option':options[i]
					});
				}
			} else {
				data = {"total":0, "rows":[]};  // initialization if there is no options
			}
					
			$('#dgOption').datagrid('loadData', data);  // load options for the selected parameter
		}
	});
	
	$('#dgParameter').datagrid('getPanel').bind('keypress', function(e){  // keypress event handler
		var keyCode = e.keyCode ? e.keyCode : e.which;
		
		switch (keyCode) {
		case 13:
			var selection = $('#dgParameter').datagrid('getSelected');
			
			if (selection) {
				var index = $('#dgParameter').datagrid('getRowIndex', selection);
				$('#dgParameter').datagrid('endEdit', index);  // after edit, turn off editor
			}
			break;
		case 34:
			alert('Double Quote is not allowed');
			return false;
			break;
		}
	});
	
	$('#dgOption').datagrid({  // construct datagrid for option
		columns:[[{
			field:'option', title:'Parameter Option', width:100, editor:'text'
		}]],
		toolbar:[{
			id:'btAddOption',
			iconCls:'icon-cog-add',
			disabled:true,
			text:'Add Option',
			handler:function(){
				$('#dgOption').datagrid('appendRow', {
					option:''
				});
				
				var lastIndex = $('#dgOption').datagrid('getRows').length - 1;
				$('#dgOption').datagrid('selectRow', lastIndex);
				$('#dgOption').datagrid('beginEdit', lastIndex);
			}
		},{
			id:'btDeleteOption',
			iconCls:'icon-cog-delete',
			disabled:true,
			text:'Delete Option',
			handler:function(){
				var selected = $('#dgOption').datagrid('getSelected'); // get selected row
				
				if (selected) {
					var index = $('#dgOption').datagrid('getRowIndex', selected); // get the index
					$('#dgOption').datagrid('deleteRow', index);
				}
				
				var options = [];   // array to contain option list
				var rows = $('#dgOption').datagrid('getRows'); 
				
				for (var i = 0; i < rows.length; i++) {
					options.push(rows[i].option);  // add options
				}
				
				var update = getRowInEditMode($('#dgParameter'), 'name');  // row to be updated
				update.options = options.join('|');  // update option field
			}
		}],
		fitColumns:true,
		singleSelect:true,
		rownumbers:true,
		fit:true,
		border:0,
		onSelect:function(index, rowData){
			var rows = $(this).datagrid('getRows');
			
			for (var i = 0; i < rows.length; i++) {  // turn off edit box
				$(this).datagrid('endEdit', i);
			}
			
			if ($('#bEdit').linkbutton('options').text == 'Save') {
				$(this).datagrid('beginEdit', index);  // turn on edit box for the selected row
				var ed = $(this).datagrid('getEditor', {index:index, field:'option'});  // get the editor
				$(ed.target).focus();  // focus the editor
			}
		},
		onAfterEdit:function(rowIndex, rowData, changes){
			var options = [];  // array to contain option list
			var rows = $(this).datagrid('getRows');
			
			for (var i = 0; i < rows.length; i++) {
				options.push(rows[i].option);  // add options
			}
			
			var update = getRowInEditMode($('#dgParameter'), 'name');  // row to be updated
			
			if (update) {
				update.options = options.join('|');  // update parameter options
			}
		}
	});
	
	$('#dgOption').datagrid('getPanel').bind('keypress', function(e){  // keypress event handler
		var keyCode = e.keyCode ? e.keyCode : e.which;
		
		switch (keyCode) {
		case 13:
			var selection = $('#dgOption').datagrid('getSelected');
			
			if (selection) {
				var index = $('#dgOption').datagrid('getRowIndex', selection);
				$('#dgOption').datagrid('endEdit', index);  // after edit, turn off editor
			}
			break;
		case 34:
			alert('Double Quote is not allowed');
			return false;
			break;
		}
	});
	
	$('#idMethod').html(strID);  // displaye method id
	$('#packageName').html(strPackage);  // display package name
	$('#methodName').html(strMethod);  // display method name
	
	$.post('src/testscript/getMethodDescription.php?classname=' + strPackage + '&methodname=' + strMethod, function(result) {  // load test API description & exmaple
		data = JSON.parse(result);
		document.getElementById('taDescription').value = data[0].description;  // load description
		document.getElementById('taExample').value = data[0].example;  // load example
	});
	
	$.post('src/testscript/getMethodParameters.php?packagename=' + strPackage + '&methodname=' + strMethod, function(result) {
		data = JSON.parse(result);
		$('#dgParameter').datagrid('loadData', data);
	});
	
	$('#dgOption').datagrid('loadData', []);  // initialization
}

function getRowInEditMode(e, field) {
	var rows = e.datagrid('getRows');   // rows of data
	
	for (var i = 0; i < rows.length; i++) {
		var editor = e.datagrid('getEditor', {index:i, field:field});

		if (editor) {
			return rows[i];
		}
	}
	
	return null;
}


/*
 * editMethod()
 * edit test API method
 * Jung Soo Kim
 */
function editMethod() {
	var nMethod = $('#mtree').tree('getSelected');  // get method node
	
	if (nMethod && $('#mtree').tree('isLeaf', nMethod.target)) {  // if selected node is method, open edit page
		$('#scriptTabs').tabs('select', 'Test API Detail View'); 
	} else {
		alert('Select method');
	}
}

/*
 * scriptEditDlg functions
 * It returns the string after populating all the parameters from the propertygrid
 * Jungsoo Kim 
 */
function getParameterValue() {
	var list = [];	// array to contain values temporarily
	var rows;		// rows of record in property grid
	
	rows = $('#scriptEditDlg_param').propertygrid('getRows');  // get total record rows
	
	list.push('class=' + trim($('#scriptEditDlg_cname').text()));  // get class name
	list.push('method=' + trim($('#scriptEditDlg_fname').text()));  // get method name
	
	for (var i = 0; i < rows.length; i++) {
		list.push(rows[i].name + '=' + rows[i].value); // get each parameter and value
	}
	
	return list.join('<br>');  // return class, method and parameters
}

function trim(string){
	return string.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}


/*
 * ScriptQueue functions
 * Jungsoo Kim
 */
function parseMethodInfo(className, functionName, description, example) {
	divContent = '<p class="class">Package<img alt="" src="themes/icons/box_closed.png" /></p>' +
				 '<p class="className">' + className + '</p>' +
				 '<p class="function">Member Function<img alt="" src="themes/icons/cog.png" /></p>' +
				 '<p class="functionName">' + functionName + '</p>' +
				 '<p class="label">Description</p>' +
				 '<p class="text">' + description + '</p>' +
				 '<p class="label">Example</p>' +
				 '<p class="text">' + example + '</p>';
	$("#memfunc").html(divContent);
}

function updateMethodHelpPanel(content) {
	var cname;				// class name
	var fname;  			// method name
	var data;				// array to contain decoded JSON
	
	cname = getValue('class', content);  // get class name from the cell content
	fname = getValue('method', content); // get method name from the cell content
	
	if (cname != null && fname != null) {
		$.post('src/testscript/getMethodDescription.php?classname=' + cname + '&methodname=' + fname, function(result) {  // get description & example
			data = JSON.parse(result);
			parseMethodInfo(cname, fname, data[0].description.replace(/\n/gi, '<br>'), data[0].example.replace(/\n/gi, '<br>')); // parse on the method help panel
		});
	} else {
		divContent = '<p style="font-weight:bold;"><img alt="" src="themes/icons/information.png" />&nbsp;Instruction</p>' +
					 '<p>1. Click member function from Test Module Library tree in the right frame.</p>' +
					 '<p>2. Drag this block to the right table to add member function.  If you select a row and drag, existing block ' + 
					 'will be replace with this block.</p>';
		$('#memfunc').html(divContent);
	}
}

function editScript(cellString) {
	var cname;									// class name
	var fname;  								// method name
	var parameters = {"total":0, "rows":[]};	// parameter JSON string
	var value;									// value of a parameter
	
	//cname = getValue('class', cellString);  // get class name from the cell content
	//fname = getValue('method', cellString); // get method name from the cell content
	
	cname = $('#memfunc').find('p.className:eq(0)').text();  // get class name from the method help panel
	fname = $('#memfunc').find('p.functionName:eq(0)').text();  // get method name from the method help panel
	
	if ((fname != null && fname.length > 0) || (cname != null && cname.length > 0)){
		$('#scriptEditDlg_cname').html('<img alt="" src="themes/icons/box_closed.png" /> ' + cname);
		$('#scriptEditDlg_fname').html('<img alt="" src="themes/icons/cog.png" /> ' + fname);
		
		$.post('src/testscript/getMethodParameters.php?methodname=' + fname + '&packagename=' + cname, function(result){	// get parameters
			data = JSON.parse(result);
			value = getValue('delay', cellString);  // get delay value

			if (value == null) {
				value = '00:00:00';  // by default, set the value to '00:00:00'
			}
			
			parameters.rows.push({
				"name":'delay',
				"value":value,
				"editor":{
					"type":"timespinner",
					"options":{
						"showSeconds":true,
						"highlight":2
					}
				}
			});
			
			for (var i = 0; i < data.length; i++) {
				value = getValue(data[i].name, cellString);  // get value of each parameter
				
				if (value == null) {
					value = '';  // by default, set the value as empty string
				}
				
				switch (data[i].type) {
					case 'text':
						parameters.rows.push({
							"name":data[i].name,
							"value":value,
							"editor":{
								"type":'text'
							}
						});
						break;
					case 'numberspinner':
						parameters.rows.push({
							"name":data[i].name,
							"value":value,
							"editor":{
								"type":"numberspinner"
							}
						});
						break;
					case 'datetimebox':
						parameters.rows.push({
							"name":data[i].name,
							"value":value,
							"editor":{
								"type":"datetimebox"
							}
						});
						break;
					case 'timebox':
						parameters.rows.push({
							"name":data[i].name,
							"value":value,
							"editor":{
								"type":"timespinner"
							}
						});
						break;
					case 'combobox':						
						parameters.rows.push({
							"name":data[i].name,
							"value":value,
							"editor":{
								"type":"combobox",
								"options":{
									"data":createComboOptions(data[i].options, '|'),
									"onSelect":function(record){
    									if (comboMultipleSelection.indexOf(record.text) == -1) {  // if not selected before, add to the list
    										comboMultipleSelection.push(record.text);
    									}

   										$(this).combobox('setValue', comboMultipleSelection.join());
   									},
    								"onLoadSuccess":function(){
    									var selectedRow = $('#scriptEditDlg_param').propertygrid('getSelected');

    									if (selectedRow.value.length > 0) {
    										comboMultipleSelection = selectedRow.value.split(',');
    									} else {
    										comboMultipleSelection = [];
    									}
    								}
								}
							}
						});
						break;
				}
			}
			
			$('#scriptEditDlg_param').propertygrid({
				fit:true,
				border:0
			}).propertygrid('loadData', parameters);
			
			$('#scriptEditDlg_param').datagrid('getPanel').bind('keypress', function(e){  // keypress event handler
				var keyCode = e.keyCode ? e.keyCode : e.which;
				
				switch (keyCode) {
				case 13:
					var selection = $('#scriptEditDlg_param').datagrid('getSelected');
					
					if (selection) {
						var index = $('#scriptEditDlg_param').datagrid('getRowIndex', selection);
						$('#scriptEditDlg_param').datagrid('endEdit', index);  // after edit, turn off editor
					}
					break;
				case 34:
					alert('Double Quote is not allowed');
					return false;
					break;
				}
			});
			
			$('#scriptEditDlg').dialog('open');
		});
	} else {
		alert('No method is selected');
	}
}

function getValue(parameter, source) {
	var value = null;												// value of a parameter
	var pattern = new RegExp("(" + parameter + "=)+[^<]+");	// create regexp to parse value of a parameter

	matches = source.match(pattern);  // find matched strings for a given parameter
	
	if (matches != null) {
		value = matches[0].replace(matches[1], '');  // parse value
	}
	
	return value;
}

function createComboOptions(string, deliminator) {
	var list = string.split(deliminator);
	var option = [];
	
	if (string.length > 0){
    	for (var i = 0; i < list.length; i++) {
    		option.push({
    			"value":list[i],
    			"text":list[i]
    		});
    	}
	}
	
	return option;
}


















//testscript.js

//Date created 11/11/2011
//Author : Snigdha Sivadas (wvpg48)
//Client functions for testscript functionality 
   var selected_methods = new Array();  //array stores list of json file names corresponding to methods selected for test script  

   var  groupdeleted = "false";
  
  
	
	function ShowContent(txtstr) {
		var newA = txtstr;
		var tc1 = "";
		var tc2 = "";
		var actionarr = new Array(); 
		var actionhead = "";
		var j = 0;
		for(var k in txtstr){
			  //alert(txtstr[k]);
			  ft =	txtstr[k];
			 // ft = ft.replace(/(\\r\\n)/gm,"<br>");
		  
			  if(k == "TestCaseName"){
				  tc1 =tc1+"<p>"+k+":</p>";
			  }
			  else if(k == "CaseDescription"){
				 // tc2 =tc2+"<p>"+k+":</p>";
				  tc2=k;
			  }
			 else{
				  
				  actionhead = actionhead+'<th class="styleth">'+k+'</th>';
				  if(ft.length>0){
						
						var data1 =  trimtc(ft).split("\\r\\n");
						var len = data1.length;
						var i=0;
						while (i < len){
							if(j==0){actionarr[i]=""; }
							if(trimtc(data1[i]).length<1){data1[i] = ""; }
							actionarr[i] = actionarr[i]+'<td class="styletd" contenteditable="true">'+data1[i]+'</td>';
							i++;
						}
								
					}
				  
				  j++;
			  }
		
		}
		
		
		var tabcon =  new ConstructTable(actionhead,actionarr);
		var samHTML= tabcon.getHTML();
		//alert(samHTML);
		
		
		
		$('#testcaseid').html(newA.TestCaseName);
		$('#tc2').html('<h1 class="ph1">'+tc2+'</h1>');
		$('#testcasedesc').html(newA.CaseDescription);
		//$('#testproc').html(samHTML);
		$('#coltable').html(samHTML);
		/*
		LoadXML(newA.TestCaseName);
		LoadPropertyGrid(newA.TestCaseName);*/
	}
	
	function ShowXmlData(txtxml){
		
		if(trimtc(txtxml)=="FALSE"){txtxml=" Test Script does not exists for the testcase";}
		else txtxml = txtxml.replace(/</gi, "\n<");
		 $('#testxml').html('<h1 class="ph1">'+ 'XML Test Script Viewer' +'</h1>');
		 $('#txttar').html('<textarea id="xmlview" class="styled" readonly></textarea>');
		 $('#xmlview').text(txtxml);
	}
	
	function ShowcontentCreateScript(txtstr) {
		var newA = txtstr;
		$('#file_name').html(newA[0]);
		$('#method_name').html(newA[1]);

	}
	
	//To remove the blank spaces
	function trimtc(str){
		str = str.replace(/^\s\s*/, ''),
		ws = /\s/,
		i = str.length;
		while (ws.test(str.charAt(--i)));
		return str.slice(0, i + 1);
	}
	
	function specialCharacters(str){
			str = str.replace(/&/gi, '%26');
			str = str.replace(/\+/gi,'%2B');
			str = str.replace(/\#/gi,'%23');

		return str;
	}
	
   function specialCharactersC(str){
		str = str.replace(/&amp;/gi, '%26');
		str = str.replace(/\+/gi,'%2B');

	return str;
   }
	
	function specialChar(str){
		str = str.replace(/\"/gi, '\\"');
		return str;
	}
	
	function specialConvert(str){
		str = str.replace(/&amp;/gi, '\&');
		return str;
	}
	//function to handle the response that contains all the test suite, to add the corresponding test suite to function
	function HandleResponse_testcase(response, testsuitename) {
			//alert("testdata"+response);
			return  eval('(' + response + ')');
	}
	//--------------------------------------------------------------------------------------
	// loadpropertiesgrid
	// Author: Snigdha
	//--------------------------------------------------------------------------------------
	function loadpropertiesgrid(attr) {
		//var myJSONText;
		var testid = document.getElementById("testcaseid").innerHTML;
		if(testid.length<10){
			$.messager.alert("Test Case Navigator"," Please select the test case on the left panel !");
		}
		else{
			/*if(!include1(selected_methods, attr)) {
				selected_methods.push(attr);
				var myJSONText = JSON.stringify(selected_methods, ",");
				MakeRequest_AppendFiles(myJSONText, attr,selected_methods);
			}*/
				len = selected_methods.length;
				selected_methods.push(attr+'__'+len);
				var myJSONText = JSON.stringify(selected_methods, ",");
				MakeRequest_AppendFiles(myJSONText, attr,selected_methods);
			
		}
	}
	
	
    function HandleResponse_test_script2(testscript_status){
        if(testscript_status=="FALSE")
        {
        	$.messager.alert("Create Test Script","File Could Not be Created Error");
        }
    }

    function HandleResponse_test_script1(testscript_status,id){  
      	
       if(testscript_status == "FALSE" ){
    	   $.messager.alert ("Create Test Script","File Could Not be Created Error !");
        }
      else if(testscript_status == "TRUE" ){
    	  $.messager.alert ("Create Test Script","Test Script for test id "+specialConvert(id)+"   is created!");
        }
      }
    
    /*
    function MakeRequest_Methods(package_name){
		var xmlHttp1 = getXMLHttp1();
		xmlHttp1.open("GET", "src/testscript/get_methods.php?package_name="+package_name, false);
		xmlHttp1.send(null);
		var resp = eval('(' + xmlHttp1.responseText + ')');
		return resp;
    }
    
    function MakeRequestPackages(framework,cid) {
		var xmlHttp1 = getXMLHttp1();
		xmlHttp1.open("GET", "src/testscript/get_package.php?framework_name=" +framework+"&coreid=" + cid, false);
		xmlHttp1.send(null);
		var resp = eval('(' + xmlHttp1.responseText + ')');
		return resp;
	
	} - to be deleted JKIM */
    
    /*
    function MakeRequestFramework() {
		var xmlHttp1 = getXMLHttp1();
		xmlHttp1.onreadystatechange = function() {
			if(xmlHttp1.readyState == 4){	
				var resp = eval('(' + xmlHttp1.responseText + ')');
				$("#modules").tree('loadData',resp);			
			}
		}
		xmlHttp1.open("GET", "src/testscript/get_framework.php", false);
		xmlHttp1.send(null);
	}*/
    
 // To clear the property Grid
	function testscriptclear(flag){
		if(flag==1){
			cleargrid();
		}
		else if(flag==0){
			$.messager.confirm("Clear Test Script ", "Do you want to clear the test script "+"  ?",function(r){
				if(r){
					 cleargrid();
				 }	
			});
		}
	}
	
	
	function cleargrid(){
		var data = [];
		selected_methods = new Array();
		$('#propertygridtestcase').propertygrid({
				height : 400,
				showGroup : true,
				scrollbarSize : 0
				}).propertygrid('loadData', data);
	}
	
	function testscriptdelete(type){
		var testid = document.getElementById("testcaseid").innerHTML;
		testid = specialCharactersC(testid);
		
		if(testid.length<10){
			$.messager.alert('Delete Test Script','Please select the test case on the left panel!');
		}
		else{
			var xmlfile=testid+".xml";
			//var checkbool = MakeRequest_testcasexml(xmlfile);
			if(trimtc(MakeRequest_testcasexml(xmlfile))=="FALSE"){
				testscriptclear(1);
				$.messager.alert("Delete Test Script","Test script "+xmlfile+" does not exists!");
			}
			else{
				$.messager.confirm("Delete Test Script", "Do you want to delete "+xmlfile+"  ?",function(r){
					if(r){
						var xmlHttp1 = getXMLHttp1();
						xmlHttp1.onreadystatechange = function() {
							if(xmlHttp1.readyState == 4){
								testscriptclear(1);
								LoadXML(testid);		
							}
						};
						xmlHttp1.open("GET", "src/testscript/destroy_testcasesproperties.php?xmlfilename="+xmlfile, false);
						xmlHttp1.send(null);
					}
				});
			 }
		}
		
	}
	
	function test_script_deletetestcase(){
		var testid = document.getElementById("testcaseid").innerHTML;
		
		if(testid.length<10){
			$.messager.alert("Test Case Navigator"," Please select the test case on the left panel !");
		}
		else{ 
			//var xmlfile=testid+".xml";
			var row = $('#propertygridtestcase').propertygrid('getSelected'); 
			if (row == null){
				$.messager.alert("Delete Test Case "," Please select the test function to be deleted from the grid !");
			}
			else{
				
				
				var groupname = row.group;
				//alert(groupname);
				var element = $('#propertygridtestcase').propertygrid('getRows');
				var mything = new PropertyGridRemove(element,groupname);
				
				
				var data = mything.getJSON();
				var filename = mything.getfilename();
				
				//alert("process value "+mything.getprocess());
				selected_methods = removedata(filename,selected_methods);
				//var myObject = eval('(' + data + ')');
				
				var mything1 = new PropertyGridReOrganization(data);
				var myObject = mything1.getJSON();
				
				
				
				
				$('#propertygridtestcase').propertygrid({
					//width : 800,
					height : 400,
					showGroup : true,
					scrollbarSize : 0
				}).propertygrid('loadData',myObject);
				
				// Remove saving on Remove test case
				/*var xmlres = MakeRequest_testcasexml(xmlfile);
				if (trimtc(xmlres) != "FALSE" ){
					test_script_generation(1);
				}*/
				
			}
		}
		
	}
	
	function MakeRequest_AppendFiles(filenamesjson, current,selected_methods) {
		var data =[];
		var xmlHttp = getXMLHttp1();
		var len1 = selected_methods.length ;
		
		//var len =0 ;
	    if (len1 > 1){
	    	len = $('#propertygridtestcase').propertygrid('getRows').length;
	    }
		//var indexgroup = 0;
		var element = $('#propertygridtestcase').propertygrid('getRows');
		//var my_group = {};
		xmlHttp.onreadystatechange = function() {
		if(xmlHttp.readyState == 4){
			//if(len1 == 1){
			  // alert(selected_methods[0]);
			//}
				
				if(len1 == 1){
					//data = xmlHttp.responseText;
					element=[];
				}
				//else {
				    //alert("selected_methods"+selected_methods);
					data = processtoAppendData(eval('(' + xmlHttp.responseText + ')'),element);
					
				//}
				
			
					
				var myObject = eval('(' + data + ')');
				$('#propertygridtestcase').propertygrid({
					//width : 800,
					height : 400,
					showGroup : true,
					scrollbarSize : 0
				}).propertygrid('loadData',myObject);
			
			}
		};
		
		xmlHttp.open("GET", "src/testscript/get_testcasesproperties.php?jsonfile_name=" + filenamesjson +"&currentappend=" + current +"&selectedmethods=" + selected_methods, false);
		xmlHttp.send(null);
	}
	
	
	function LoadXML(testid){
	   	 var testfilename = testid + ".xml";
	  	 var xmlfile = MakeRequest_testcasexml(testfilename);
	  	 ShowXmlData(xmlfile);
    	 }
	
   function LoadPropertyGrid(testid){
	   var testfilename = testid + ".xml";
	   var data = MakeRequest_testcasexmljson(testfilename);
	   //alert("Load Property Grid"+data);
	   var myObject =[];
	  
	   
	   if (trimtc(data) != "FALSE" ){
		   //var sep = trimtc(data).indexOf("inxpackage=");
		   var data1 =  trimtc(data).split("inxpackage=");
		   if(data1.length >1 ){
		    	selected_methods = data1[1].split(",");
		    }
		   	myObject = eval('(' + data1[0] + ')');
	   }
	   
		$('#propertygridtestcase').propertygrid({
			//width : 800,
			//height : 400,
			showGroup : true,
			scrollbarSize : 0
		}).propertygrid('loadData',myObject);
   }  	 
	
	
	
	function test_script_generation(type) {
		//var xmlHttp = getXMLHttp1();
		var testid = document.getElementById("testcaseid").innerHTML;
		var testidm = testid;
		var responsemessage = false;
		var startflag = false;
		// var testfilename = testid+".xml";
		//testid = testid.replace(/ /gi, "_");
		var testfilename = testid + ".xml";
		var testdesc = document.getElementById("testcasedesc").innerHTML;
		var groupname;
		var groupindex = -1;
		//validate all the input data
		//if((testidm == "Test Case Id-No Selection Yet") || ($('#propertygridtestcase').propertygrid('getRows') == 0)) {
		if(testidm.length<10){
			$.messager.alert("Test Case Navigator"," Please select the test case on the left panel !");
		}
		else if(($('#propertygridtestcase').propertygrid('getRows') == 0)&& (type == 0)){
			$.messager.alert("Test Module Library"," Please select the test case from Test Module Library");
		}
		else if(($('#propertygridtestcase').propertygrid('getRows') == 0)&& (type == 1)){
			testscriptdelete(1);
		}
		else {
			//added to end edit
			var element = $('#propertygridtestcase').propertygrid('getSelected');
			var indexrow = $('#propertygridtestcase').propertygrid('getRowIndex', element);
		
			$('#propertygridtestcase').propertygrid('endEdit', indexrow);
			$('#propertygridtestcase').propertygrid('beginEdit', indexrow);
			$('#propertygridtestcase').propertygrid('endEdit', indexrow);
			
			//create a XML file
			MakeRequest_CreateFile(testfilename, testidm);
			len = $('#propertygridtestcase').propertygrid('getRows').length;
			var indexgroup = 0;
			var element = $('#propertygridtestcase').propertygrid('getRows');
			var my_group = {};
			groupname = element[0].group;
			while(indexgroup < len) {
				if(indexgroup == 0)
					my_group[element[indexgroup].name] = element[indexgroup].value;
				else if(element[indexgroup - 1].group == element[indexgroup].group){
					my_group[element[indexgroup].name] = element[indexgroup].value;
				}
				
			 // alert (element[indexgroup].name+"  "+element[indexgroup].value);
				
				if((element[indexgroup].group != groupname) || (indexgroup == len - 1)){
					var myJSONText1 = JSON.stringify(my_group, ",");
					//var datajson = JSON.parse(myJSONText1);
					var mySplitResult = groupname.split('(')[0];
					if(indexgroup == len - 1)
						responsemessage = true;
					
					MakeRequest_WriteFile(testfilename, testidm, testdesc, mySplitResult, myJSONText1, responsemessage,startflag,++groupindex);
					startflag= true;
					
					
					if(indexgroup < len) {
						groupname = element[indexgroup].group;
						my_group = {};
						my_group[element[indexgroup].name] = element[indexgroup].value;
					}

				}
				indexgroup++;

			}
			
			LoadXML(testidm);
		}
		
		/*
		 * Added by Jungsoo.  Refresh the parent node of the selected testcase to reflect the change of automation icon
		 * March-02-2012
		 */
		var node = $("#treeTestCase").tree('getSelected');
		var parent = $("#treeTestCase").tree('getParent', node.target);
		
		if (parent) {
			$("#treeTestCase").tree('reload', parent.target);
		} else {
			$("#treeTestCase").tree('reload');
		}
		
	}
	
	
	
 //function to get all the test cases for a particular test suite
	function MakeRequest_testcase(coreid,groupname){
		var xmlHttp = getXMLHttp1();
		xmlHttp.onreadystatechange = function() {
			if(xmlHttp.readyState == 4){	
				//alert (type+groupname+" Make_TestCase "+xmlHttp.responseText);
				//return xmlHttp.responseText;
				var resp = "";
				resp = eval('(' + xmlHttp.responseText + ')');
				if(type==1){
				  $("#treeTestCase").tree('loadData',resp);
				}
				else{
					$('#treeTestCase').tree('loadData',[]);
					$('#treeTestCase').tree('loadData',resp);
					
				}
			}
		};
		
		xmlHttp.open("GET", "src/testscript/gettestscripts.php?groupname="+groupname+"&coreid="+coreid, false);
		xmlHttp.send(null);
	   
	}

	
	
	function MakeRequest_testcase1(testsuitename){
		var xmlHttp = getXMLHttp1();
		testsuitename = specialCharacters(testsuitename);
		xmlHttp.open("GET", "src/testscript/gettestcases.php?test_suite1=" + testsuitename, false);
		xmlHttp.send(null);
		return HandleResponse_testcase(xmlHttp.responseText, testsuitename);

	}
	
	function MakeRequest_testcase2(testcasename){
		var xmlHttp = getXMLHttp1();
		testcasename = specialCharacters(testcasename);
		xmlHttp.open("GET", "src/testscript/gettestcasesdetails.php?test_case="+testcasename, false);
		xmlHttp.send(null);
		return HandleResponse_testcase(xmlHttp.responseText, testcasename);
	}
	
	function MakeRequest_testcasexml(xmlfile){
		var xmlHttp = getXMLHttp1();
		xmlfile = specialCharactersC(xmlfile);
		xmlfile = specialCharacters(xmlfile);
		xmlHttp.open("GET", "src/testscript/get_testcasesxml.php?xmlfile_name="+xmlfile, false);
		xmlHttp.send(null);
		return xmlHttp.responseText;
		
	}
	
	function MakeRequest_testcasexmljson(xmlfile){
		var xmlHttp = getXMLHttp1();
		xmlfile = specialCharactersC(xmlfile);
		xmlfile = specialCharacters(xmlfile);
		xmlHttp.open("GET", "src/testscript/get_testcasesxmljson.php?xmlfile_name=" + xmlfile, false);
		xmlHttp.send(null);
		//alert ("xmlHttp.responseText"+xmlHttp.responseText);
		return xmlHttp.responseText;
	}
	
	function MakeRequest_CreateFile(testfilename, testid) {
		var xmlHttp = getXMLHttp1();
		testid = specialCharactersC(testid);
		 xmlHttp.onreadystatechange = function() {
			if(xmlHttp.readyState == 4){
				HandleResponse_test_script2(trimtc(xmlHttp.responseText));
			}
		};
		 
		xmlHttp.open("GET", "src/testscript/create_test_script.php?testfilename="+testfilename+"&testid="+testid,false);
		xmlHttp.send(null);
	}

	function MakeRequest_WriteFile(testfilename, testid, testdesc, groupname1, datajson1, responsemessage,startflag,gp) {
		datajson1 = specialCharacters(datajson1);
		testid = specialCharactersC(testid);
		var xmlHttp = getXMLHttp1();
		xmlHttp.onreadystatechange = function() {
			if(xmlHttp.readyState == 4){
				if(responsemessage){
					HandleResponse_test_script1(trimtc(xmlHttp.responseText), testfilename);
				}

			}
		};
		
		//alert ("group index "+gp);
		xmlHttp.open("GET", "src/testscript/write_test_script.php?testfilename=" + testfilename + "&testid=" + testid + "&testdesc=" + testdesc + "&groupname1=" + groupname1 + "&datajson1=" + datajson1 + "&respo=" + responsemessage+"&startflag=" + startflag+ "&groupindex=" + gp, false);
		xmlHttp.send(null);
	}
	
   function processtoAppendData(currentjson,datafromgrid){
	  var mything = new PropertyGridAppend(currentjson,datafromgrid);
	  return mything.getJSON();
   }	   
   
   //To check the element exists in the array
   function include1(arr, obj) {
		return (arr.indexOf(obj) != -1);
   }
   
   function removedata(filen,arr){
	   var len = arr.length;
	   var arr1=[];
	   var i = 0;
	   var j= -1;
	   while(i<len){
		   if (arr[i]!=filen){
			   j++;
			   arr1[j]=arr[i];	   
		   }
		   i++;	   
	   }
	   
	   return arr1;
   }
  
   
    function getXMLHttp1() {
	    var xmlHttp;
	
	    try {
		//Firefox, Opera 8.0+, Safari
		xmlHttp = new XMLHttpRequest();
	    }
	    catch(e) {
		//Internet Explorer
			try {
			    xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch(e) {
			    try {
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			    }
			    catch(e) {
				alert("Your browser does not support AJAX!");
				return false;
			    }
			}
	    }
	    
	    return xmlHttp;
	}
    
    
      function PropertyGridAppend(cui, data) {
    	 
        cjson  = cui;
        currdata = data;
        
        this.getJSON=function() {
            var cjson1 = currdata;
            var len ;
            var editorstr;
        	var consJSON = "[";
        	var i = 0 ;
        	len = this.getdatalength();
        	editorstr = "";
        	while (i < len){
        		consJSON = consJSON+"{";
        		if (typeof cjson1[i].editor == "object"){
        			editorstr = processDataSpinner(cjson1[i].editor);
        			}
        		else 
        			{editorstr = '"'+cjson1[i].editor+'"' ; }
        		consJSON = consJSON+"name:"+'"'+cjson1[i].name+'"'+",value:"+'"'+specialChar(cjson1[i].value)+'"'+",group:"+'"'+cjson1[i].group+'"'+",editor:"+editorstr;
        		consJSON = consJSON+"}";
        		i++;
        		consJSON = consJSON+","; 
        	}
        	j = 0;
        	cjson1 = cjson;
        	len = this.getcjsonlength();
        	
        	editorstr = "";
        	while (j < len){
        		consJSON = consJSON+"{";
        		if (typeof cjson1[j].editor == "object"){
        			editorstr = processDataSpinner(cjson1[j].editor);
        		}
        		else 
        			{editorstr = '"'+cjson1[j].editor+'"'; }
        		consJSON = consJSON+"name:"+'"'+cjson1[j].name+'"'+",value:"+'"'+specialChar(cjson1[j].value)+'"'+",group:"+'"'+cjson1[j].group+'"'+",editor:"+editorstr;
        		consJSON = consJSON+"}";
        		j++;
        		if (j < len){ consJSON = consJSON+","; }
        	}
        	
        	consJSON = consJSON+"]";
        	return consJSON;
       };
        
       this.getcjsonlength=function(){
    	    return cjson.length;
       };
       
       this.getdatalength=function(){
    		  return  currdata.length;
       };
       
      }
      
      
      function PropertyGridRemove(cui,groupname) {
          cjson1  = cui; 
          var editorstr;
          var spac = null;
          var process = false;
          this.getJSON=function() {    
            var len ;
          	var consJSON = "[";
          	var i = 0 ;
          	len = this.getcjsonlength();
          	editorstr = "";
          	while (i < len){
          	  if(cjson1[i].group != groupname){
          		consJSON = consJSON+"{";
          		
        		if (typeof cjson1[i].editor == "object"){
        			editorstr = processDataSpinner(cjson1[i].editor);
        			}
        		else 
        			{editorstr = '"'+cjson1[i].editor+'"' ; }
          		consJSON = consJSON+"name:"+'"'+cjson1[i].name+'"'+",value:"+'"'+specialChar(cjson1[i].value)+'"'+",group:"+'"'+cjson1[i].group+'"'+",editor:"+editorstr;
          		consJSON = consJSON+"}";
          		if (i < len){ consJSON = consJSON+","; }
          	  }
          	  else {
          		  if(cjson1[i].name=="package"){
          			    //spac= cjson1[i].value;
          			  	//spac = spac.replace(/\./g,'_');
          			  	//spac = spac+"__"+groupname+".json";
          			      spac = groupname;
          			      process = true;
          			    //To modify for multiple support 
          			      //spac = spac.replace(/\./g,'_')+".json";
          			      
          			    //  commented to modify the mutiple
          			      //spac = spac.replace(/\./g,'_');
          		  }     	    		  
          		  
          	  }
          		i++;  		
          	  
          	}
         	consJSON = consJSON+"]";
          	return consJSON;
         };
          
         this.getcjsonlength=function(){
      	    return cjson1.length;
         };
         
         this.getfilename=function(){
        	 return spac;
         };
       
         this.getprocess=function(){
        	 return process;
         };
         
         
        }
      
      function PropertyGridReOrganization(jsonstr1){
    	  var jsonstr = eval('(' + jsonstr1 + ')');
    	  var myString1 = "";
    	  var myString = "";
    	  var mystr = new Array(); 
    	  len = selected_methods.length;
    	  var selected_methods_new = new Array();
    	  var copy = false;
    	  this.getJSON=function() { 
    		  count = 0;
    		  
    	  while (count < len ){
    		      copy = false;
    			  myString = selected_methods[count];
    			  mystr = myString.split("__");
    			 // alert ("My String ->"+mystr[0]+"__"+mystr[1]+"__"+mystr[2]);
    			  if ( mystr[2] == count ){
    				  selected_methods_new.push(myString);
    			  }
    			
    			  else{
    				  myString1 = mystr[0]+"__"+mystr[1]+"__"+count;
    			     // alert ("this new "+myString1);
     			      var i = 0;
    			      len1 = this.getcjsonlength();
    			      while (i < len1){
    			    	  //alert ("test 233444 (1)  ==="+ jsonstr[i].group +"'"+ myString);
    			    	  if(jsonstr[i].group.trim() == myString.trim() ){
    			    		  if (copy == false ){
    			    			  selected_methods_new.push(myString1);
    			    			  copy= true;
    			    		   }
    			    		  //alert ("233444 (2) ==="+ jsonstr[i].group +"'"+ myString1);
    			      		  jsonstr[i].group = myString1;
    			    		  //alert ( "54353434-(3)===="+jsonstr[i].group +"'"+ myString1);
    			    	  }
    			    	  
    			    	  i++;
       			      }
    			  }
    	 			  count++;
    	      }
    		  
    		  
    	      selected_methods = new Array();
    	      selected_methods = selected_methods_new;
    	      return jsonstr;
    	      
    	  };
    	  
    	  
    	  
    	  	this.getcjsonlength=function(){
        	    return jsonstr.length;
           };
      }
      
      function ConstructTable(chead,arr1) {
          carray  = arr1;   
          var ft;
          this.getHTML=function() {    
            var len ;
          	//var consHTML = '<table class="stylesample"><tr>'+chead+'</tr>';
        	var consHTML = '<tr>'+chead+'</tr>';
          	var i = 0 ;
          	len = this.getarraylength();
          	while (i < len){
          		ft = carray[i].replace(/(\n)/gm,"<br>");
          		ft = ft.replace(/&amp;gt;/gm,">");
          		ft = ft.replace(/&amp;lt;/gm,"<");
          		consHTML = consHTML+'<tr>'+ft+'</tr>';
          		i++;
           	}
          	//consHTML = consHTML+"</table>";
          	//consHTML = consHTML;
          	return consHTML;
         };
          
         this.getarraylength=function(){
      	    return carray.length;
         };
       
        }
      
      
      
   
      
      
      function processDataSpinner(cjson){
    	 var cjson1 = cjson;
    	 var jsonstr = '{';
    	 var counter = 0; 
    	  for(var k in cjson1){
    		 // alert("key "+k+"  "+cjson1[k]);
    		  if(jsonstr.length>5){
    			  jsonstr = jsonstr+',';
    		  }
    		  jsonstr = jsonstr+'"'+k+'":';
    		  if (typeof cjson1[k]=="object"){
    			  jsonstr = jsonstr+'{';
    			  var cjson12 = cjson1[k];
    			  for(var j in cjson12){
    				  if(counter==1){
    					  jsonstr = jsonstr+',';
    				  }
    				  if ((j=="increment")||(j=="min")||(j=="max")||(j=="highlight")||(j=="editable")){
    					  jsonstr = jsonstr+'"'+j+'":'+cjson12[j];
    				  }
    				  else{
    					  jsonstr = jsonstr+'"'+j+'":'+'"'+cjson12[j]+'"';
    				  }
    				  counter=1;
    				//  alert("key1 "+j+"  "+cjson12[j]);
    			  }
    			  jsonstr = jsonstr+'}';
    		  }
    		  else {
    			  jsonstr = jsonstr+'"'+cjson1[k]+'"';
    		  }
    	  }
    	  jsonstr = jsonstr+'}';
    	  return jsonstr;
    	 
    	  
      }
      
/******************************************************************************************************************
 * XMLDisplay javascript
 ******************************************************************************************************************/   
function LoadXML(ParentElementID,URL) {
	var xmlHolderElement = GetParentElement(ParentElementID);
  	
	if (xmlHolderElement==null) { 
		return false; 
	}
  	
	ToggleElementVisibility(xmlHolderElement);
  	
	return RequestURL(URL,URLReceiveCallback,ParentElementID);
}

function LoadXMLDom(ParentElementID,xmlDoc) {
	if(xmlDoc) {
		var xmlHolderElement = GetParentElement(ParentElementID);
  		
		if (xmlHolderElement==null) { return false; }
  		
		while (xmlHolderElement.childNodes.length) { 
			xmlHolderElement.removeChild(xmlHolderElement.childNodes.item(xmlHolderElement.childNodes.length-1));	
		}
  		
		var Result = ShowXML(xmlHolderElement,xmlDoc.documentElement,0);
  		//var ReferenceElement = document.createElement('div');
  		var Link = document.createElement('a');		
  		Link.setAttribute('href','http://www.levmuchnik.net/Content/ProgrammingTips/WEB/XMLDisplay/DisplayXMLFileWithJavascript.html');
  		//var TextNode = document.createTextNode('Source: Lev Muchnik');
  		//Link.appendChild(TextNode);
  		xmlHolderElement.appendChild(Link);
  		
  		return Result;
  	} else { 
  		return false; 
  	}
}
  
function LoadXMLString(ParentElementID,XMLString) {
  	xmlDoc = CreateXMLDOM(XMLString);
  	return LoadXMLDom(ParentElementID,xmlDoc) ;
}

////////////////////////////////////////////////////////////
// HELPER FUNCTIONS - SHOULD NOT BE DIRECTLY CALLED BY USERS
////////////////////////////////////////////////////////////
function GetParentElement(ParentElementID) {
  	if (typeof(ParentElementID)=='string') {
  		return document.getElementById(ParentElementID);	
  	} else if (typeof(ParentElementID)=='object') { 
  		return ParentElementID;
  	} else { 
  		return null; 
  	}
}
  
function URLReceiveCallback(httpRequest,xmlHolderElement) {
	try {
		if (httpRequest.readyState == 4) {
			if (httpRequest.status == 200) {
				var xmlDoc = httpRequest.responseXML;
				
				if (xmlHolderElement && xmlHolderElement!=null) {
					xmlHolderElement.innerHTML = '';
  					return LoadXMLDom(xmlHolderElement,xmlDoc);
  				}
			} else {
				return false;
			}
		}
	} catch( e ) {
		return false;
	}	
}

function RequestURL(url,callback,ExtraData) { // based on: http://developer.mozilla.org/en/docs/AJAX:Getting_Started
	var httpRequest = null;
  
	if (window.XMLHttpRequest) { // Mozilla, Safari, ...
		httpRequest = new XMLHttpRequest();
  
		if (httpRequest.overrideMimeType) { 
			httpRequest.overrideMimeType('text/xml'); 
		}
	} else if (window.ActiveXObject) { // IE
		try { 
			httpRequest = new ActiveXObject("Msxml2.XMLHTTP");   
		} catch (e) {
			try { 
				httpRequest = new ActiveXObject("Microsoft.XMLHTTP"); 
			} catch (e) {}
		}
	}
  
	if (!httpRequest) {
		return false;   
	}
	
	httpRequest.onreadystatechange = function() { callback(httpRequest,ExtraData); };
	httpRequest.open('GET', url, true);
	httpRequest.send('');
  		return true;
}
  

function CreateXMLDOM(XMLStr) {
  	if (window.ActiveXObject) {
  		xmlDoc=new ActiveXObject("Microsoft.XMLDOM"); 
  		xmlDoc.loadXML(XMLStr);	
	  
  		return xmlDoc;
  	} else if (document.implementation && document.implementation.createDocument) {
  		var parser=new DOMParser();
  		return parser.parseFromString(XMLStr,"text/xml");
  	} else {
  		return null;
  	}
}		

  
var IDCounter = 1;
var NestingIndent = 15;

function ShowXML(xmlHolderElement,RootNode,indent) {
	if (RootNode==null || xmlHolderElement==null) { 
		return false; 
	}
  	
	var Result  = true;
  	var TagEmptyElement = document.createElement('div');
  	TagEmptyElement.className = 'Element';
  	TagEmptyElement.style.position = 'relative';
  	TagEmptyElement.style.left = NestingIndent+'px';

  	if (RootNode.childNodes.length==0) { 
  		var ClickableElement = AddTextNode(TagEmptyElement,'','Clickable') ;
  		ClickableElement.id = 'div_empty_' + IDCounter;	  
  		AddTextNode(TagEmptyElement,'<','Utility') ;
  		AddTextNode(TagEmptyElement,RootNode.nodeName ,'NodeName');
  
  		for (var i = 0; RootNode.attributes && i < RootNode.attributes.length; ++i) {
  			CurrentAttribute  = RootNode.attributes.item(i);
  			AddTextNode(TagEmptyElement,' ' + CurrentAttribute.nodeName ,'AttributeName') ;
  			AddTextNode(TagEmptyElement,'=','Utility') ;
  			AddTextNode(TagEmptyElement,'"' + CurrentAttribute.nodeValue + '"','AttributeValue') ;
  		}
  
  		AddTextNode(TagEmptyElement,' />') ;
  		xmlHolderElement.appendChild(TagEmptyElement);	

  		//SetVisibility(TagEmptyElement,true);    
  	} else { // mo child nodes
  		var ClickableElement = AddTextNode(TagEmptyElement,'+','Clickable') ;
  		ClickableElement.onclick  = function() {
  			ToggleElementVisibility(this); 
  		};
  
  		ClickableElement.id = 'div_empty_' + IDCounter;	
	
  		AddTextNode(TagEmptyElement,'<','Utility') ;
  		AddTextNode(TagEmptyElement,RootNode.nodeName ,'NodeName');
  
  		for (var i = 0; RootNode.attributes && i < RootNode.attributes.length; ++i) {
  			CurrentAttribute  = RootNode.attributes.item(i);
  			AddTextNode(TagEmptyElement,' ' + CurrentAttribute.nodeName ,'AttributeName') ;
  			AddTextNode(TagEmptyElement,'=','Utility') ;
  			AddTextNode(TagEmptyElement,'"' + CurrentAttribute.nodeValue + '"','AttributeValue') ;
  		}

  		AddTextNode(TagEmptyElement,'>  </','Utility') ;
  		AddTextNode(TagEmptyElement,RootNode.nodeName,'NodeName') ;
  		AddTextNode(TagEmptyElement,'>','Utility') ;
  		xmlHolderElement.appendChild(TagEmptyElement);	
  		SetVisibility(TagEmptyElement,false);
  		//----------------------------------------------
  
  		var TagElement = document.createElement('div');
  		TagElement.className = 'Element';
  		TagElement.style.position = 'relative';
  		TagElement.style.left = NestingIndent+'px';
  		ClickableElement = AddTextNode(TagElement,'-','Clickable') ;
  		ClickableElement.onclick  = function() {
  			ToggleElementVisibility(this); 
  		};
  
  		ClickableElement.id = 'div_content_' + IDCounter;		
  		++IDCounter;
  		AddTextNode(TagElement,'<','Utility') ;
  		AddTextNode(TagElement,RootNode.nodeName ,'NodeName') ;
  
  		for (var i = 0; RootNode.attributes && i < RootNode.attributes.length; ++i) {
  			CurrentAttribute  = RootNode.attributes.item(i);
  			AddTextNode(TagElement,' ' + CurrentAttribute.nodeName ,'AttributeName') ;
  			AddTextNode(TagElement,'=','Utility') ;
  			AddTextNode(TagElement,'"' + CurrentAttribute.nodeValue + '"','AttributeValue') ;
  		}
  
  		AddTextNode(TagElement,'>','Utility') ;
  		TagElement.appendChild(document.createElement('br'));
  		var NodeContent = null;
  
  		for (var i = 0; RootNode.childNodes && i < RootNode.childNodes.length; ++i) {
  			if (RootNode.childNodes.item(i).nodeName != '#text') {
  				Result &= ShowXML(TagElement,RootNode.childNodes.item(i),indent+1);
  			} else {
  				NodeContent =RootNode.childNodes.item(i).nodeValue;
  			}					
  		}			
  
  		if (RootNode.nodeValue) {
  			NodeContent = RootNode.nodeValue;
  		}
  
  		if (NodeContent) {	
  			var ContentElement = document.createElement('div');
  			ContentElement.style.position = 'relative';
  			ContentElement.style.left = NestingIndent+'px';			
  			AddTextNode(ContentElement,NodeContent ,'NodeValue') ;
  			TagElement.appendChild(ContentElement);
  		}			
  		
  		AddTextNode(TagElement,'  </','Utility') ;
  		AddTextNode(TagElement,RootNode.nodeName,'NodeName') ;
  		AddTextNode(TagElement,'>','Utility') ;
  		xmlHolderElement.appendChild(TagElement);	
  	}

  	// if (indent==0) { ToggleElementVisibility(TagElement.childNodes(0)); } - uncomment to collapse the external element
  	return Result;
}
  
function AddTextNode(ParentNode,Text,Class) {
  	NewNode = document.createElement('span');
  	
  	if (Class) {  
  		NewNode.className  = Class;
  	}
  	
  	if (Text) { 
  		NewNode.appendChild(document.createTextNode(Text)); 
  	}
  	
  	if (ParentNode) { 
  		ParentNode.appendChild(NewNode); 
  	}
  	
  	return NewNode;		
}
  
function CompatibleGetElementByID(id) {
  	if (!id) { 
  		return null; 
  	}
  	
  	if (document.getElementById) { // DOM3 = IE5, NS6
  		return document.getElementById(id);
  	} else {
  		if (document.layers) { // Netscape 4
  			return document.id;
  		} else { // IE 4
  			return document.all.id;
  		}
  	}
}

function SetVisibility(HTMLElement,Visible) {
  	if (!HTMLElement) { 
  		return; 
  	}
  	
  	var VisibilityStr  = (Visible) ? 'block' : 'none';

  	if (document.getElementById) { // DOM3 = IE5, NS6
  		HTMLElement.style.display =VisibilityStr; 
  	} else {
  		if (document.layers) { // Netscape 4
  			HTMLElement.display = VisibilityStr; 
  		} else { // IE 4
  			HTMLElement.id.style.display = VisibilityStr; 
  		}
  	}
}
  
function ToggleElementVisibility(Element) {
  	if (!Element|| !Element.id) { 
  		return; 
  	}
  	
  	try {
  		ElementType = Element.id.slice(0,Element.id.lastIndexOf('_')+1);
  		ElementID = parseInt(Element.id.slice(Element.id.lastIndexOf('_')+1));
  	} catch(e) { 
  		return ; 
  	}

  	var ElementToHide = null;
  	var ElementToShow= null;

  	if (ElementType=='div_content_') {
  		ElementToHide = 'div_content_' + ElementID;
  		ElementToShow = 'div_empty_' + ElementID;
  	} else if (ElementType=='div_empty_') {
  		ElementToShow= 'div_content_' + ElementID;
  		ElementToHide  = 'div_empty_' + ElementID;
  	}
  	
  	ElementToHide = CompatibleGetElementByID(ElementToHide);
  	ElementToShow = CompatibleGetElementByID(ElementToShow);
  	
  	if (ElementToHide) { 
  		ElementToHide = ElementToHide.parentNode;
  	}
  	
  	if (ElementToShow) { 
  		ElementToShow = ElementToShow.parentNode;
  	}
  	
  	SetVisibility(ElementToHide,false);
  	SetVisibility(ElementToShow,true);
}



function uploadwindow(suitename) {
        var w = 400;
        var h = 200;
        var left = (screen.width/2) - (w/2);
        var top = (screen.height/2) - (h/2);
	//alert(suitename);
        var targetWin = window.open("src/testscript/upload_main.php?username="+username+"&suitename="+suitename, 'win_upload', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, addressbar=no, width='+w+', height='+h+', top='+top+', left='+left);


}