


/*******************************************************************************************
 * removeRunlist()
 * Jung Soo Kim
 * April 30, 12
 * It removes the selected row from the Runlist
 */
function removeRunlist(){
	// select all the checked rows
	var selections = $("#tctable").datagrid('getSelections');
	
	// update temp JSON file
	$.post('src/runlist/getdata.php', {remove:selections}, function(result) {
		$('#tctable').datagrid('reload'); // reload the datagrid
	});
}


function moveRunlist(arrow) {
	// make sure only 1 row is selected
	var selections = $('#tctable').datagrid('getSelections');
	
	if (selections.length != 1) {
		$.messager.show({
			title:'invader+',
			msg:'Please select only 1 row to move UP or DOWN'
		});
	} else {
		// turn off sorting name
		$('#tctable').datagrid({
			sortName:null
		});
		
		// get the current index
		var selected = $('#tctable').datagrid('getSelected');
		
		// update temp JSON file
		$.post('src/runlist/getdata.php', {move:arrow, selection:selected}, function(result) {
			$('#tctable').datagrid('reload'); // reload the datagrid
		});
	}
}


/**********************************************************************************************
 * applyConfigure()
 * Jung Soo Kim
 * April 30, 12
 * It applies the settings of execution
 */
function applyConfigure(){
	// get values from the settings
	var valueIteration = $("#iteration").numberspinner('getValue');
	//var valueDuration = $("#duration").timespinner('getValue');
	var valueDelay = $("#delay").timespinner('getValue');
	
	var selections = $("#tctable").datagrid('getSelections');
	
	/*
	for (var i = 0; i < selections.length; i++){
		// get row index
		var row = $("#tctable").datagrid('getRowIndex', selections[i]);
		
		// upodate the row
		$("#tctable").datagrid('updateRow', {
			index:row,
			row:{
				count:valueIteration,
				delay:valueDelay
				//duration:valueDuration
			}
		});
	}*/
	
	// save temp JSON file
	$.post('src/runlist/getdata.php', {updateArray:selections, delay:valueDelay, iteration:valueIteration}, function(result) {
		$('#tctable').datagrid('reload'); // reload the datagrid
		// unselect all
		$("#tctable").datagrid('unselectAll');
	});
}


	
/************************************************************************************************
 * reset()
 * Jung Soo Kim
 * April 30, 12
 * Changes iteration, duration and delay to defalt value
 */
function reset(){
	$("#iteration").numberspinner('setValue', 1);
	$("#duration").timespinner('setValue', 0);
	$("#delay").timespinner('setValue', 0);
}


/************************************************************************************************
 * addRunlist()
 * Jung Soo Kim
 * April 30, 12
 * It adds selected node to the datagrid
 */
function addRunlist(){
	var data;
	
	// check the size of row data of the datagrid
	if ($('#tctable').datagrid('getRows').length > 0) {
		data = $('#tctable').datagrid('getRows');
	} else {
		data = [];
	}
	
	// get selected cycle node
	var node = $('#treeTestPlan').tree('getSelected');
	//var cyclename = node.text;
	
	// get children of the selected node
	var children = $('#treeTestPlan').tree('getChildren', node.target);
	
	for (var i = 0; i < children.length; i++) {
		var isChecked = children[i].checked;
		var testId = children[i].id;
		var testDesc = children[i].text.replace(/.*<\/span> - /, '');
		
		if (isChecked) {
			var test = {
					testid:testId,
					testname:testDesc,
					count:'1',
					delay:'00:00:00'
					//duration:'00:00:00'
			};
			
			// find the test element in the array
			var index = findElement(test, data);

			if (index == -1) {
				data.push(test);
			}
		}
	}
	
	// save temporary file
	$.post('src/runlist/addRunlist.php', {rows:data}, function(result) {
		if (result == "SUCCESS") {
			// reload the datagrid
			$('#tctable').datagrid('load');
		} else {
			alert(result); // display error message
		}
	});
}


/****************************************************************************************
 * findElement
 * @param element
 * @param array
 * @returns {Number}
 * Jung Soo Kim
 * April 30, 12
 * It finds an element in an array and return the index
 */
function findElement(element, array) {
	var index = -1; // initialization, if not found return -1
	
	for (var i = 0; i < array.length; i++) {
		if (array[i].testid == element.testid &&
			array[i].testname == element.testname) {
			index = i;
			break;
		}
	}
	
	return index;
}


