<?php 
/*
 * Author: Snigdha Sivadas
 * Description: Framework UI
 */

?> 

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="keywords" content="jquery,ui,easy,easyui,web">
	<meta name="description" content="Admin Page for InvaderPlus!">
	<title>jQuery EasyUI CRUD Demo</title>
	<link rel="stylesheet" type="text/css" href="../../themes/default/easyuiInvaderPlus.css">
	<link rel="stylesheet" type="text/css" href="../../themes/icon.css">
	

	<style type="text/css">
		#fm{
			margin:0;
			padding:10px 30px;
		}
		.ftitle{
			font-size:14px;
			font-weight:bold;
			color:#666;
			padding:5px 0;
			margin-bottom:10px;
			border-bottom:1px solid #ccc;
		}
		.fitem{
			margin-bottom:5px;
		}
		.fitem label{
			display:inline-block;
			width:80px;
		}
	</style>
	<script type="text/javascript" src="../../lib/jquery-1.6.min.js"></script>
	<script type="text/javascript" src="../../lib/jquery.easyui.min.js"></script>
	
   
	<script type="text/javascript">
		var url;
		function newFramework(){
			$('#dlg').dialog('open').dialog('setTitle','New Framework');
			$('#fm').form('clear');
			url = 'framework.php?type=save';
		}
		function editFramework(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
				$('#dlg').dialog('open').dialog('setTitle','Edit User');
				$('#fm').form('load',row);
				url = 'framework.php?type=edit&id='+row.id+'&fid='+row.framework_id;
			}
		}
		function saveFramework(){
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
		function removeFramework(){
			var row = $('#dg').datagrid('getSelected');
			if (row){
				$.messager.confirm('Confirm','Are you sure you want to remove this Framework?',function(r){
					if (r){
						$.post('framework.php?type=delete',{id:row.id,fid:row.framework_id},function(result){
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
	</script>
</head>
<body>
	<h2>Admin  Page - Framework<?php  echo  $_SESSION['username']; ?> </h2>
	<div> <?php  print_r($_SESSION) ?> </div>
	<div style="width:800px;height:auto;padding:5px;">  

	
	<table id="dg" title="FrameWork" class="easyui-datagrid" style="width:700px;height:250px"
			url="framework.php?type=load"
			toolbar="#toolbar" pagination="true"
			rownumbers="true" fitColumns="true" singleSelect="true">
		<thead>
			<tr>
				<th field="framework_id" width="100">Framework ID</th>
				<th field="framework_name" width="100">Framework Name</th>
			</tr>
		</thead>
	</table>
	<div id="toolbar">
		<a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newFramework()">New Framework</a>
		<a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editFramework()">Edit Framework</a>
<!--		<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="removeFramework()">Remove Framework</a>-->
	</div>
	
	<div id="dlg" class="easyui-dialog" style="width:400px;height:280px;padding:10px 20px"
			data-options="closed:'true', buttons:'#dlg-buttons'">
		<div class="ftitle">Framework Information </div>
		<form id="fm" method="post">
			<div class="fitem">
			    <hidden name="framework_id">
				<label>Framework Name:</label>
				<input name="framework_name" class="easyui-validatebox" required="true">
			</div>
			
		</form>
	</div>
	<div id="dlg-buttons">
		<a href="#" class="easyui-linkbutton" iconCls="icon-ok" onclick="saveFramework()">Save</a>
		<a href="#" class="easyui-linkbutton" iconCls="icon-cancel" onclick="javascript:$('#dlg').dialog('close')">Cancel</a>
	</div>
  
  
   
 </div>
</body>
</html>
