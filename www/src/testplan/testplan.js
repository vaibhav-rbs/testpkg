function localPlanLoad(){
	$('#workingPlan').combobox({
                url:'src/testplan/get_working_plan.php?user_name='+username,
                valueField:'id',
                textField:'text'
	});
}

function create_local_master_plan(){

	var group = $('#groupCombo').combobox('getText');
	//alert("group="+group);
	var product = $('#productCombo').combobox('getText');
	//alert("product="+product);
	var scope = document.getElementById('scope').value;
	//alert("scope="+scope);
	var start = $('#startDate').datebox('getValue');
	//alert("start="+start);
	var end = $('#endDate').datebox('getValue');
	//alert("end="+end);

	//Check empty data
	if(group == "--Select group--"){
		alert("No group is selected. Failed to save!");
		exit;
	}
	if(product == "--Select product--"){
		alert("No product is set. Failed to save!");
		exit;
	}
	if(!scope){
		alert("No scope is set. Failed to save!");
		exit;
	}
	if(!start){
		alert("No start date is set. Failed to save!");
		exit;
	}
	if(!end){
		alert("No end date is set. Failed to save!");
		exit;
	}

	var master_plan = product + " MASTER - " + scope + " (" + group + ")";
	//alert("master_plan="+master_plan);

	var win = $.messager.progress({
		title:'Please wait',
		text:'Processing...',
		interval:'900'
        });

	$.post('src/testplan/save_plan_local.php',{plantype:"Master",username:username,plan:master_plan,start:start,end:end}, function(result){
                $.messager.progress('close');
		if (result.msg){
			alert(result.msg);
		}else{
			alert("Master plan "+master_plan + " is created locally. Please export to test central with or without test cases later!");
			localPlanLoad();
		}

	},'json');



}




function create_local_cycle_plan(){

	var group = $('#groupCombo2').combobox('getText');
	//alert("group="+group);
	var master = $('#masterCombo').combobox('getText');
	//alert("master plan="+master);
	var start = $('#startDate2').datebox('getValue');
	//alert("start="+start);
	var end = $('#endDate2').datebox('getValue');
	//alert("end="+end);

	if(group == "--Select group--"){
		alert("No group is selected. Failed to save!");
		exit;
	}
	if(master == "--Select master plan--"){
		alert("No master plan is set. Failed to save!");
		exit;
	}
	if(!start){
		alert("No start date is set. Failed to save!");
		exit;
	}
	if(!end){
		alert("No end date is set. Failed to save!");
		exit;
	}
	var win = $.messager.progress({
		title:'Please wait',
		text:'Processing...',
		interval:'900'
        });

	$.post('src/testplan/save_plan_local.php',{plantype:"Cycle",username:username,plan:master,start:start,end:end}, function(result){
                $.messager.progress('close');
		if (result.msg){
			alert(result.msg);
		}else{
			alert("Cycle plan "+master+" is created locally. Please export to test central with or without test cases later!");
			localPlanLoad();
		}
	},'json');

}


function save_local_plan_tests(){

	var plan = $('#workingPlan').combobox('getText');
	var tableContent = JSON.stringify($('#testplanDatagrid').datagrid('getData'));
	//alert(tableContent);
	if ( plan == "--Select a working plan--" || plan == "default" || plan == null){
		alert("You didn't select a working plan!");
	}else{		
		$.messager.progress({text:'Saving...'});
		$.post('src/testplan/save_local_tests.php', {all_tests:tableContent,plan:plan,username:username}, function(result) {
			$.messager.progress('close');
			if(result.msg)alert(result.msg);
		},'json');

	}
}




function submit_tc_plan_tests(){
	var plan = $('#workingPlan').combobox('getText');
	var tableContent = JSON.stringify($('#testplanDatagrid').datagrid('getData'));
	//alert(tableContent);
	if ( plan == "--Select a working plan--" || plan == "default" || plan == null){
		alert("You didn't select a working plan!");
	}else{		
		$.messager.progress({text:'Exporting...'});
		$.post('src/testplan/submit_to_tc_tests.php', {all_tests:tableContent,plan:plan,username:username}, function(result) {
			$.messager.progress('close');
			if(result.msg)alert(result.msg);
			localPlanLoad();
                        var data = {"total":0, "rows":[]};
                        $('#testplanDatagrid').datagrid('loadData',data);

		},'json');

	}
}




function remove_local_plan(){
	var plan = $('#workingPlan').combobox('getText');
	if ( plan == "--Select a working plan--" || plan == "default" || plan == null){
		alert("You didn't select a working plan!");
	}else{		
		$.messager.progress({text:'Removing...'});
		$.post('src/testplan/remove_local_plan.php', {plan:plan,username:username}, function(result) {
			$.messager.progress('close');
			if(result.msg)alert(result.msg);
			localPlanLoad();
			var data = {"total":0, "rows":[]};
			$('#testplanDatagrid').datagrid('loadData',data);
		},'json');

	}
}



function remove_test(){
	/*
	 * Jung Soo; change to delete multiple selections
	 */
	var selections = $('#testplanDatagrid').datagrid('getSelections');
	
	for (var i in selections) {
		var rowNum = $('#testplanDatagrid').datagrid('getRowIndex', selections[i]);
		$('#testplanDatagrid').datagrid('deleteRow', rowNum);
	}
}
