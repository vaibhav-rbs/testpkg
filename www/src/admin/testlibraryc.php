<?php 
/*
 * Author: Snigdha Sivadas
 * Description: testlbrary UI
 */

?> 

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>

<title>Admin </title>
<link rel="stylesheet" type="text/css"
	href="../../themes/default/easyuiInvaderPlus.css">
<link rel="stylesheet" type="text/css" href="../../themes/icon.css">


<style type="text/css">
#fm {
	margin: 0;
	padding: 10px 30px;
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
<script type="text/javascript" src="../../lib/jquery-1.6.min.js"></script>
<script type="text/javascript" src="../../lib/jquery.easyui.min.js"></script>


<script type="text/javascript">
		var url;
		function newTestlibrary(){
			$('#dlg').dialog('open').dialog('setTitle','New Test Library');
			$('#fm').form('clear');
			url = 'testlibrary.php?type=save';
		}
		function editTestlibrary(){
			var row = $('#dg').datagrid('getSelected');
			
			$('#cb1').combobox({  
			    url:'interadmin.php?type=lpack',  
        		valueField:'package_name',
        		textField:'package_name'
			});

			$('#cb').combobox({data:[{id:'y',text:'yes'},{id:'n',text:'no'}]});
			 
			if (row){
				$('#dlg').dialog('open').dialog('setTitle','Edit User');
				$('#fm').form('load',row);
				url = 'testlibrary.php?type=edit&id='+row.id;
			}

		}
		
		function saveTestlibrary(){
			$('#fm').form('submit',{
				url: url,
				onSubmit: function(){
					return $(this).form('validate');
				},
				success: function(result){
					var result = eval('('+result+')');
					if (result.success){
						$('#dlg').dialog('close');		// close the dialog
						$('#dg').datagrid('reload');	// reload the user data
					} else {
						$.messager.show({
							title: 'Error',
							msg: result.msg
						});
					}
				}
			});
		}
		function removeTestlibrary(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
				$.messager.confirm('Confirm','Are you sure you want to remove this Test Library?',function(r){
					if (r){
						$.post('testlibrary.php?type=delete',{id:row.id,fid:row.framework_id},function(result){
							if (result.success){
								$('#dg').datagrid('reload');
								} else {
								$.messager.show({	// show error message
									title: 'Error',
									msg: result.msg
								});
							}
						},'json');
					}
				});
			}
		}
		
		function doSearch(){  
		    $('#dg').datagrid('load',{  
		    	tname: $('#tname').val(),  
		        pname: $('#pname').val()  
		    });  
		}  
				

		
		
	</script>
</head>
<body>
	<? echo $_REQUEST['coreid']; ?>
	<h2>Admin Page - Test Library  </h2>
	<div style="width: auto; height: auto; padding: 5px;">
		  
		

		<table id="dg" title="Test Library" class="easyui-datagrid"
			style="width: auto; height: 500px" url="testlibrary.php?type=load"
			toolbar="#toolbar" pagination="true" rownumbers="true"
			fitColumns="true" singleSelect="true">
			<thead>
				<tr>
					<th field="test_id" width="10">Test ID</th>
					<th field="package_id" width="10">Package Id</th>
					<th field="package_name" width="200">Package Name</th>
					<th field="test_methodname" width="100">Test Methodname</th>
					<th field="test_method" width="100">Test Method</th>
					<th field="showflag" width="60" align="center">Published</th>
					<th field="test_description" width="300">Test Description</th>
					<th field="test_example" width="300">Test Example</th>
				</tr>
			</thead>
		</table>
		<div id="toolbar">
			<span>Package Name:</span>  
	    	<input id="pname" > 
	    	
	    	<span>Test Method Name:</span>  
	    	<input id="tname">  
	    	 
	    	<a href="#" class="easyui-linkbutton" plain="true" onclick="doSearch()">Search</a>  
			
			<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true"
				onclick="newTestlibrary()">New TestMethod</a> <a href="#"
				class="easyui-linkbutton" iconCls="icon-edit" plain="true"
				onclick="editTestlibrary()">Edit TestMethod</a> <a href="#"
				class="easyui-linkbutton" iconCls="icon-remove" plain="true"
				onclick="removeTestlibrary()">Remove TestMethod</a>
		</div>

		<div id="dlg" class="easyui-dialog"
			style="width: 600px; height: 600px; padding: 10px 20px" data-options="closed='true', buttons='#dlg-buttons'">
			<div class="ftitle">Test Library Information</div>
			<form id="fm" method="post">
				<div class="fitem">
					<label>Test ID </label>
					<input name="test_id" readonly>
					<!--  <hidden name="package_id"> -->
					<br>
					 <label>Package
						Name:</label> <!--  <input name="package_name" class="easyui-validatebox" style="width:400px" required="true"> -->
					<input id="cb1"   class="easyui-combobox" name="package_name" style="width: 400px" ><br>
					
					<label>Test MethodName:</label> <input name="test_methodname"
						class="easyui-validatebox" style="width: 400px" required="true"> <br>
					<label>Test Method:</label> <input name="test_method"
						class="easyui-validatebox" style="width: 600px" required="true"> <br>

					<label>Display the method to the user:</label>
					<input id="cb" class="easyui-combobox"name="showflag" valueField="id"  textField="text">
					<br>
					<label>Test Description:</label> <textarea name="test_description"
						class="easyui-validatebox" style="width: 500px; height: 100px"
						required="true"> </textarea> <br>
					<label>Test Example:</label> <textarea name="test_example"
						class="easyui-validatebox" style="width: 500px; height: 100px"
						required="true">  </textarea>
				</div>
			</form>
		</div>
		<div id="dlg-buttons">
			<a href="#" class="easyui-linkbutton" iconCls="icon-ok"
				onclick="saveTestlibrary()">Save</a> <a href="#"
				class="easyui-linkbutton" iconCls="icon-cancel"
				onclick="javascript:$('#dlg').dialog('close')">Cancel</a>
		</div>
	</div>
</body>
</html>
