function draw_bar(list){

   var result_arr;
   var input_arr = [];
   var pkg_arr = [];
   //var input_str;
   var pkg_str;
   var num = list.length;

   for (var i in list) {

        if ( i < num) {
   			result_arr = [];
			result_arr.push(list[i].P);
			result_arr.push(list[i].F);
			result_arr.push(list[i].B);
			result_arr.push(list[i].I);
			result_arr.push(list[i].N);
			pkg_str = list[i].name;
			input_arr.push(result_arr);
			pkg_arr.push(pkg_str);
	}

   }


   var bar3 = new RGraph.Bar('myCanvas2', input_arr);
   bar3.Set('chart.units.post', ' tests');
   bar3.Set('chart.colors', ['green', 'red', 'orange', 'blue', 'gray']);
   bar3.Set('chart.gutter.left', 200);
   bar3.Set('chart.gutter.right', 50);
   bar3.Set('chart.gutter.top', 50);
   bar3.Set('chart.gutter.bottom', 200);
   bar3.Set('chart.shadow', true);
   bar3.Set('chart.shadow.color', '#aaa');
   bar3.Set('chart.background.barcolor1', 'white');
   bar3.Set('chart.background.barcolor2', 'white');
   bar3.Set('chart.background.grid.hsize', 1);
   bar3.Set('chart.background.grid.vsize', 1);
   bar3.Set('chart.grouping', 'stacked');
   bar3.Set('chart.labels', pkg_arr);
   bar3.Set('chart.labels.above', true);
   bar3.Set('chart.key', ['Pass', 'Fail', 'Blocked' , 'Indeterminated' , 'Not Run']);
   bar3.Set('chart.key.background', 'rgba(255,255,255,0.7)');
   bar3.Set('chart.key.position', 'gutter');
   bar3.Set('chart.key.position.gutter.boxed', false);
   bar3.Set('chart.key.position.y', 5);
   bar3.Set('chart.key.position.x', 300);
   bar3.Set('chart.key.border', false);
   bar3.Set('chart.background.grid.width', 0.1); // Decimals are permitted
   bar3.Set('chart.text.angle', 35);
   bar3.Set('chart.strokestyle', 'rgba(0,0,0,0,0)');
   RGraph.Clear(bar3.canvas); // This function also calls the RGraph.ClearAnnotations() function
   bar3.Draw();

}





function draw_pie(list){


   var pie2 = new RGraph.Pie('myCanvas1', [list.P, list.F, list.B, list.I, list.N]); // Create the pie object
   pie2.Set('chart.labels', [list.P+' tests', list.F+' tests', list.B+' tests', list.I+' tests', list.N+' tests']);
   pie2.Set('chart.colors', ['green', 'red', 'orange', 'blue' , 'gray']);
   pie2.Set('chart.key', ['Pass ('+list.P2+'%)', 'Fail ('+list.F2+'%)', 'Blocked ('+list.B2+'%)', 'Indeterminated ('+list.I2+'%)','Not Run ('+list.N2+'%)']);
   pie2.Set('chart.title.vpos', 0.5);
   pie2.Set('chart.key.background', 'white');
   pie2.Set('chart.align', 'left');
   pie2.Set('chart.strokestyle', '#aaa');
   pie2.Set('chart.linewidth', 1);
   pie2.Set('chart.exploded', [1,1,1,1,1]);
   pie2.Set('chart.gutter.left', 200);
   pie2.Set('chart.gutter.right', 50);
   pie2.Set('chart.gutter.top', 5);
   pie2.Set('chart.gutter.bottom', 10);
   RGraph.Clear(pie2.canvas);
   pie2.Draw();

}

function show_summary_cycle(result){
   var build;
   var exec_date;
   var create_date;

   var flag = 1;

   plan = "<p><h1>"+result.cplan+"</h1></p>";

   if (result.software_ver != null){
   	build = "<p><h4>BUILD ID:"+result.software_ver[0]+"</h4></p>";
   }else{
   	build = "<p><h4>BUILD ID: NA </h4></p>";
   }

   if(result.last_update != null){
   	exec_date = "<h4>LAST UPDATE:"+result.last_update+"</h4>";
   }else{

   	exec_date = "<h4>LAST UPDATE: NA </h4>";

   }

   if(result.created_date != null){
   	create_date = "<h4>CREATED DATE:"+result.created_date+"</h4>";
   }else{
   	create_date = "<h4>CREATED DATE: NA </h4>";
   }

   

   $('#cycle_name').html(plan);
   $('#build_id').html(build);
   $('#create_date').html(create_date);
   $('#exec_date').html(exec_date);
   $('#g1').html("<p><h2>Overall Result</h2></p>");


   $.post('src/report/get_pie_chart_info.php',{detail:result.detail}, function(result){
	if (result != null){
		if ( result.P == 0 && result.F == 0 && result.B == 0 && result.I == 0){
			alert("Cannot find results from Test Central for this cycle plan");
			flag = 0;
		}
   		draw_pie(result);
	}
   },'json');
   $('#g2').html("<p><h2>Results by Components</h2></p>");
   $.post('src/report/get_bar_chart_info.php',{detail:result.detail}, function(result){
	if (result != null){
   		draw_bar(result);
	}
   },'json');

   $.post('src/report/get_detail_string.php',{detail:result.detail}, function(result){
	if (result != null){
   		$('#detail_result_table').html(result.display);
   		
	}
   },'json');

   $('#defect_summary').html("<p><h2>Defect Summary</h2></p>");
   $.post('src/report/get_defect_info.php',{detail:result.detail, defect:result.detail_defect }, function(result){
	if (result != null){
   		$('#defect_summary_table').html(result.d_summary);
   		$('#defect_detail_table').html(result.d_detail);
   		
	}
   },'json');
   $.post('src/report/get_detail_block.php',{detail:result.detail, more_detail:result.detail_defect}, function(result){
	if (result != null && flag == 1){
   		$('#detail_info_block').html(result.display);
   		
	}else{
   		$('#detail_info_block').html("<p><h2>No Detail results</h2></p>");

	}
   },'json');

}

function show_summary_master(result){



}













