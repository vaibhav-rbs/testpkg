     function selectLoadData(){
            $('#sr').combobox({
                url:'src/execution/get_runlist.php?user_name='+username,
                valueField:'id',
                textField:'text'
             });
      }

     function selectLoadData2(){
		
	        //var view = $('#viewall').combobox('getValue');
            	//if (view == "yes"){
            		//$('#select2').combobox({
               			//url:'src/execution/get_result_plan_name.php?user_name=all',
               			//valueField:'id',
               			//textField:'text'
             		//});

               	//}else {
       	                $('#select2').combobox({
                      		url:'src/execution/get_result_plan_name.php?user_name='+username,
        			valueField:'id',
               			textField:'text'
             		});
		//}
      }


      function edit_prop(){
		var row = $('#device_dg').datagrid('getSelected');
		if(!row) {
			alert("Please select a row on Device List to edit property file");
			exit;
		}
		
		prop_file = row.device+".json";
		
                
                $.post('src/execution/check_create_prop.php',{filename:prop_file}, function(result){
			if (!result) alert("result is null");
                	$('#editprop').dialog('open').dialog('setTitle','Property Setting');
                	$('#dg_prop').propertygrid('loadData',result);
                },'json');

      }

      function save_prop(){
               	var row_device = $('#device_dg').datagrid('getSelected');
		var curr_rows = $('#dg_prop').propertygrid('getRows');
				$.messager.progress({
                      title:'Please waiting',
                      text:'Processing...',
                      interval:'600'
                });


               	$.post('src/execution/save_prop.php',{device:row_device.device,curr_rows:curr_rows}, function(result2){
				if (result2.msg == "Missing device info"){
                        		alert("Missing device info");
			     		exit;
				}
				$.messager.progress('close');
				$('#editprop').dialog('close');
	       	},'json');
        }

      function save_dev_prop(){
		var names = $('#sc').combobox('getValue');
               	$.post('src/execution/save_dev_prop.php',{names:names}, function(result){
				if (result.msg){
                        		alert(result.msg);
			     		exit;
				}
				$('#devprop').dialog('close');
	       	},'json');
		set_comp_flag = 1;
        }




      function editServer(){
                var row = $('#dg').datagrid('getSelected');
  
                if (row){
                	$('#dlg').dialog('open').dialog('setTitle','Edit Server');
                	$('#fm').form('load',row);

                	url = 'src/execution/update_server.php?selected='+row.name;
                }
            else {
                                alert("Please select a row in table to edit!");
            }
		$('#dg').datagrid('reload');
      }


      function newServer(){
                $('#dlg').dialog('open').dialog('setTitle','New Server');
                $('#fm').form('clear');
                url = 'src/execution/save_server.php';
      }

    
      function saveServer(){
                $('#fm').form('submit',{
                  url: url,
                  onSubmit: function(){
                        return $(this).form('validate');
                  },
                  success: function(result){
                        result = eval('('+result+')');
  
                        if (result.success){
                           $('#dlg').dialog('close');              // close the dialog
                           $('#dg').datagrid('reload');    // reload the user data
                        }
                        else {
                           $.messager.show({
                                title: 'Error',
                                msg: result.msg
                           });
                        }
                 }
                });
        }

        function removeServer(){
                        var row = $('#dg').datagrid('getSelected');

                        if (row){
                             $.messager.confirm('Confirm','Are you sure you want to remove this Server?',function(r){
                                if (r){
                                   $.post('src/execution/remove_server.php',{name:row.name},function(result){
                                   if (result.success){
                                        $('#dg').datagrid('reload');    // reload the user data
                                   }
                                   else {
                                        $.messager.show({       // show error message
                                        title: 'Error',
                                        msg: result.msg
                                        });
                                   }
                                   },'json');
                                 }
                              });
                        }
        }

 

        function checkAvail(){
                        var target = $('#dg').datagrid('getSelected');

                        if (target == null){
                                alert("Please select a row in table for searching availability of DUT!");
                        }
                        else {
                                alert("The list of available devices will be displayed in Device List Panel");
                        	$('#button_add').linkbutton('disable');
                        	$('#button_edit').linkbutton('disable');
                        	$('#button_remove').linkbutton('disable');
                        	$('#button_ca').linkbutton('disable');
                		$.post('src/execution/check_avail.php',{tname:target.name, username:username}, function(result){
                                        if (result.msg == "ERROR_PING"){
                                                alert("Cannot ping server "+target.name);
                                        }else{
						if (result.msg == "ERROR_HOME"){
                                                	alert("Package is not installed correctly under "+target.home);
                                        	}else {
							if(result.msg == "ERROR_ADB"){
                                                		alert("ADB cannot check devices");

							}else{
								if(result.msg == "ERROR_DEVICE"){
                                                                	alert("No device is connected to this server");
								}else{
									$('#device_dg').datagrid('loadData',result);// load the data to dev panel
								}
							}
						}
                                        }

                         		$('#button_add').linkbutton('enable');
                         		$('#button_edit').linkbutton('enable');
                         		$('#button_remove').linkbutton('enable');
                         		$('#button_ca').linkbutton('enable');
                                },'json');
                        }
        }

	var display_result;
        function sleep_some_time(){
		alert("Execution and archiving are done!");
		$('#device_dg').datagrid('loadData',display_result);// load the data to dev panel
        }
        function sleep_some_time2(){
		alert("Execution is done! Since archiving is not done yet, please don't start a new execution with the same cycle plan and same device right away. If you do, you will delete the results on test machine before archiving is finished by agent. Then, the results will be lost!");
		$('#device_dg').datagrid('loadData',display_result);// load the data to dev panel
        }

        function execution(){
                var row = $('#device_dg').datagrid('getSelected');
                if(row){
                	var runlist = $('#sr').combobox('getValue');
                	if ( runlist == "--Select runlist from list--" || runlist == "default" || runlist == null){
                        	alert("You didn't select runlist file!");
                	}else {
				$('#runlist').datagrid('reload');  //clean up show progress window
				$.post('src/execution/check_prop_exist.php',{device:row.device}, function(result){
                            		if (!result.success){
                                		alert(result.msg);
						exit;
                            		}else{
						$.post('src/execution/check_target_comp.php',{name:row.name,device:row.device,runlist:runlist}, function(result){
							if (!result.success){
                                                		alert(result.msg);
                                                		exit;
							}else{

                						alert("It will take a while to run. Please click Check Progress icon about 2 or 3 mins later.");
                                				// show correct status and runlist name on device list
                						$.post('src/execution/update_avail.php',{name:row.name,device:row.device,runlist:runlist,username:username}, function(result){
									$('#device_dg').datagrid('loadData',result);// load the data to dev panel
                    						},'json');


								$.post('src/execution/execution.php',{name:row.name,device:row.device,status:row.status,runlist:runlist}, function(result){
                            					

									if(result.msg){
                                						$.messager.show({       // show error message
                                        						title: 'Error',
                            	        						msg: result.msg
                                						});
                                						alert(result.msg);
                                                				exit;
									}
									display_result = result.content;
									$.messager.confirm('Continue to do archiving','Execution is done. It will take some time to do archiving. If you chose to continue, please do not close Browser while doing archiving. If you chose to cancel the archiving, backend agent will start archiving for you silently after 2 minutes later.',function(r){
									  

									    if (r) {
									    	$.messager.progress({
                                				title:'Please waiting',
                                				text:'Processing...',
                                				interval:'900'
                        					});
									   	$.post('src/execution/save_content.php',{name:row.name,device:row.device,runlist:runlist}, function(result2){
	
											if(result2.msg != 'success'){
                                                                                		$.messager.show({       // show error message
                                                                                        		title: 'Error',
                                                                                        		msg: result2.msg
                                                                                		});
												$.messager.progress('close');
                                                                                		alert(result2.msg);
												$('#device_dg').datagrid('loadData',display_result);// load the data to dev panel
                                                                        		}else{
												$.messager.progress('close');
												setTimeout("sleep_some_time()", 6000);
											}


									   	},'json');
									   }else{
										setTimeout("sleep_some_time2()", 60);

									   }
									});	

                    						},'json');

								$.post('src/execution/save_archive_list.php',{name:row.name,device:row.device,status:row.status,runlist:runlist}, function(result){
                    						},'json');


							}
                    				},'json');
	
					}
                    		},'json');
			}
                }else{
                         alert("Please select a row in the Device List table to do execution!");
                }
         }


        function setup_comp(){

		var row = $('#device_dg').datagrid('getSelected');
		if(!row) {
			alert("Please select a row on Device List to edit property file");
			exit;
		}

		$('#devprop').dialog('open').dialog('setTitle','Set Up Companion Device');
               	$('#sc').combobox({
	                url:'src/execution/get_companion.php?device='+row.device,
                	valueField:'id',
                	textField:'text'
             	});
	}



	function stop_exec(){
                var row = $('#device_dg').datagrid('getSelected');
                if (row == null){
                        alert("Please select a row in Device List table for aborting the execution!");
                }else{
			alert("Prepare to stop the processes.");
                	$.post('src/execution/stop_exec.php',{name:row.name,device:row.device}, function(result){
				if(!result.success){
					 alert(result.msg);
				}else{
					 alert("Processes have been stopped on test machine for "+row.device);
				}
			},'json');
		}


	}

     

         function monitor(){
                var row = $('#device_dg').datagrid('getSelected');
                var flag = 0;

                if (row == null){
                        alert("Please select a row in Device List table for checking progress!");
                }else{
			$('#button_cp').linkbutton('disable');
                	$.post('src/execution/show_progress.php',{name:row.name,device:row.device,username:username}, function(result){

                        	if(result.total == 10000){
					$('#button_cp').linkbutton('enable');
                              		flag = 1;
                              		//alert("flag=1");
                        	}

                        	if(result.msg == "companion"){
                              		alert("This device is a companion, please check result from target device");
			      		exit;
				}
                        	if(result.msg == "Not able to ping test server"){
			      		$.messager.show({
                                		title: 'Invader+',
                                		msg: 'Failed to retrieve progress status due to network problem. Will attempt to patch data 6 seconds later.',
                                		showType:'fade',
                                		timeout:2000
                              		});


                        	}else{
                              		$('#runlist').datagrid('loadData',result);// load data from json string

                              		if( flag == 0){
                                       		setTimeout("monitor()", 6000);
                                       		//alert("flag=0");
                              		}
                        	}
                        },'json');
			
                  }
          }

	  function check_error(){
                var row = $('#device_dg').datagrid('getSelected');
                if (row == null){
                        alert("Please select a row in Device List table for checking stderr messages!");
                }else{
                	$.post('src/execution/show_error.php',{name:row.name,device:row.device}, function(result){
			},'json');
		}
		var file = "tempdata/log_data/"+row.device+"_err.txt";
                // If I don't use alert call before calling popup function, most of the time, 404 NOT FOUND happens. 
                // I believe it is a timing problem

		alert("The messages of stderr of the execution will be displayed");
		popup(file);


	  }

	  function remove_from_list(){
               	var execlist = $('#select2').combobox('getValue');
               	if ( execlist == "--Select an execution from list--" || execlist == "default" || execlist == null){
                       	alert("You didn't select an execution to remove from list!");
               	}else {
               		$.post('src/execution/remove_from_list.php',{execlist:execlist}, function(result){
				selectLoadData2();
               		},'json');

		}
                $('#testcase_dg').datagrid({
                        url:"tempdata/detail.json"
                });
                $('#step_dg').datagrid({
                        url:"tempdata/step.json"
                });

	  }

	  function delete_exec(){

               	var execlist = $('#select2').combobox('getValue');
               	if ( execlist == "--Select an execution from list--" || execlist == "default" || execlist == null){
                       	alert("You didn't select an execution to delete!");
               	}else {
			$('#button_delete_exec').linkbutton('disable');
			$.messager.progress({
                  title:'Please waiting',
                  text:'Processing...',
			      interval:'600'
            });

			//var gdata = $('#testcase_dg').datagrid('getData');
               		
			$.post('src/execution/delete_exec_data.php',{execlist:execlist}, function(result){

				if(result.msg != "success"){
					alert(result.msg);
				}
				$.messager.progress('close');
				selectLoadData2();
				$('#button_delete_exec').linkbutton('enable');
				setTimeout(function(){
                               		$('#testcase_dg').datagrid({
	                                        url:"tempdata/detail.json"
                                	});
 
                        	},30);

				setTimeout(function(){
					$('#step_dg').datagrid({
						url:"tempdata/step.json"
					});
                        	},50);


               		},'json');

               }
	  }

 
          function uploadtc(){

               	var execlist = $('#select2').combobox('getValue');
               	if ( execlist == "--Select an execution from list--" || execlist == "default" || execlist == null){
                       	alert("You didn't select an execution to upload the result to Test Central!");
               	}else {
			$('#button_uptc').linkbutton('disable');
				$.messager.progress({
                                title:'Please waiting',
                                text:'Processing...',
				interval:'600'
                        });

			var gdata = $('#testcase_dg').datagrid('getData');
               		$.post('src/execution/upload_tc.php',{execlist:execlist,gdata:gdata}, function(result){

                        	if(result.msg == "mysql DB problem")
                                	alert("Mysql DB connection problem or query failed!");
                        	if(result.msg == "check number failed")
                                	alert("The number of test cases to be uploaded is bigger then the number of test cases in Test Central. Uploading is aborted!");
                        	if(result.msg == "not all testcases uploaded to TC")
                                	alert("Not all of the test cases were uploaded. Please investigate it.");
                        	if(result.msg == "success")
	               			alert("The results have been uploaded to Test Central!");
				$.messager.progress('close');
				selectLoadData2();
				$('#button_uptc').linkbutton('enable');
				setTimeout(function(){
                               		$('#testcase_dg').datagrid({
	                                        url:"tempdata/detail.json"
                                	});
 
                        	},30);

				setTimeout(function(){
					$('#step_dg').datagrid({
						url:"tempdata/step.json"
					});
                        	},50);


               		},'json');

               }
         }


         function showsteps(){
                var row = $('#testcase_dg').datagrid('getSelected');

                if (row == null){
                        alert("Please select a row in Test Case List table for showing results of steps!");
                }else{
                	$.post('src/execution/show_steps.php',{tname:row.test_case_name,resultdir:row.result_dir}, function(result){


                        	if(result.msg == "Error"){
                              		alert("Error when create step result");

                        	}else{
                              		$('#step_dg').datagrid('loadData',result);// load data from json string
                        	}
                        },'json');
                }
          }

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


          function getTestLog(val,row){
		if(row === undefined || row.corid === undefined || row.tfile === undefined) {

		}else{
			$.post('src/execution/copy_log.php',{log_file:val,user_name:row.corid}, function(result){
			},'json');
			var file = "tempdata/log_data/"+row.corid+"/"+row.tfile;
			return '<a href=\"'+file+'\" onClick="return popup(this)">View Content</a>';
		}
	 }
          function getDevLog(val,row){
		if(row === undefined || row.corid === undefined || row.tfile === undefined) {
		}else{
			$.post('src/execution/copy_log.php',{log_file:val,user_name:row.corid}, function(result){
			},'json');
			var file = "tempdata/log_data/"+row.corid+"/"+row.dfile;
			return '<a href=\"'+file+'\" onClick="return popup(this)">View Content</a>';
		}
	 }
          function getAnrLog(val,row){
		if(row === undefined || row.corid === undefined || row.tfile === undefined) {
		}else{

			if (row.afile != ""){
				$.post('src/execution/copy_anr_log.php',{log_file:val,user_name:row.corid}, function(result){
				},'json');
				var file = "tempdata/log_data/"+row.corid+"/"+row.afile;
				return '<a href=\"'+file+'\" onClick="return popup(this)">View Content</a>';
			}
		}
	 }


         $(function(){
                       $('#testcase_dg').datagrid({
                            onBeforeEdit:function(index,row){
                                 row.editing = true;
                                 updateActions();
                            },
                            onAfterEdit:function(index,row){
                                 row.editing = false;
                                 updateActions();
                            },
                            onCancelEdit:function(index,row){
                                 row.editing = false;
                                 updateActions();
                            }
                        });
          });
          function updateActions(){
                        var rowcount = $('#testcase_dg').datagrid('getRows').length;
                        for(var i=0; i<rowcount; i++){
                                $('#testcase_dg').datagrid('updateRow',{
                                        index:i,
                                        row:{action:''}
                                });
                        }
          }




          function editrow(index){
                        $('#testcase_dg').datagrid('beginEdit', index);
          }
          function saverow(index){
                        $('#testcase_dg').datagrid('endEdit', index);
          }
          function cancelrow(index){
                        $('#testcase_dg').datagrid('cancelEdit', index);
          }

          function formatAction(value,row,index){
                        if (row.editing){
                                var s = '<a href="#" onclick="saverow('+index+')">Save</a> ';
                                var c = '<a href="#" onclick="cancelrow('+index+')">Cancel</a>';
                                return s+c;
                        } else {
                                var e = '<a href="#" onclick="editrow('+index+')">Edit</a> ';
                                return e;
                        }
          }



         var results = [
                    {test_result:'P',name:'P'},
                    {test_result:'F',name:'F'},
                    {test_result:'I',name:'I'},
                    {test_result:'B',name:'B'},
         ];

         function formatTR(value){
                        for(var i=0; i<results.length; i++){
                                if (results[i].test_result == value) return results[i].name;
                        }
                        return value;
         }

	 


