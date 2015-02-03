// User Submitted Variable
var projectNumber = '672466286514';
var clientId = '672466286514.apps.googleusercontent.com';

var config = {
	'client_id' : clientId,
	'scope' : 'https://www.googleapis.com/auth/bigquery'
};


$(window).resize(function() {
	fitWindowSize();
});

$(document).ready(function() {
	fitWindowSize();
});

function fitWindowSize() {
    // fit mainContainer to body size
    //document.getElementById("mainContainer").style.height = $('body').height() - 41 + 'px';
}

function auth() {
	gapi.auth.authorize(config, function() {
		gapi.client.load('bigquery', 'v2');
		$('#client_initiated').html('BigQuery client authorized');
		$('#auth_button').fadeOut();
		$('#query_button').fadeIn();
	});
}

function listDatasets() {
	var request = gapi.client.bigquery.datasets.list({
		'projectId' : projectNumber
	});
	
	request.execute(function(response) {
		$('#result_box').html(JSON.stringify(response.result.datasets, null));
	});
}

function runQuery() {
	var q = 'select groupTypeValue1, count(testCaseName) as num_tc ' + 
			'from motorola.com:sandbox:systemtest.TCDataXFonRegression20130129 ' +
			'group by groupTypeValue1 order by groupTypeValue1 ASC;';
	
	var request = gapi.client.bigquery.jobs.query({
		'projectId' : 'motorola.com:sandbox',
		'query' : q
	});
	
	var arrLabels = [];
	var arrData = [];
	
	$.messager.progress({
		text:'Querying results from Google...',
		interval:'900'
	});
	
	request.execute(function(response) {
		$.messager.progress('close');
		
		var arrResult = JSON.parse(JSON.stringify(response, null));
		var result = '<table width=100% border=1><tr>';
		
		// create table header
		for(i in arrResult.schema.fields) {
			result += '<th>' + arrResult.schema.fields[i].name + '</th>';
		}
		result += '</tr>';
		
		for(i in arrResult.rows) {
			result += '<tr>';
			for(j in arrResult.rows[i].f) {
				result += '<td>' + arrResult.rows[i].f[j].v + '</td>';
				
				switch (j) {
					case '0':
						arrLabels.push(arrResult.rows[i].f[j].v);
						break;
					case '1':
						arrData.push(parseInt(arrResult.rows[i].f[j].v));
						break;
				}
			}
			result += '</tr>';
		}
		
		var myChart = new RGraph.HBar('cvs', arrData);
		
		myChart.Set('labels', arrLabels);
		myChart.Set('gutter.left', 200);
		myChart.Set('labels.above', true);
		myChart.Set('background.grid', false);
		myChart.Set('colors', ['#3266CC']);
		myChart.Set('events.click', function (e, shape) {
			// If you have multiple charts on your canvas the .__object__ is a reference to
	        // the last one that you created
	        var obj   = e.target.__object__;
	        var index = shape['index'];
	        var value = obj.data[index];
	        
	        alert(arrLabels[index] + ' has ' + value + ' test cases.');
		});
		myChart.Set('events.mousemove', function (e, shape) {
	        // It's automatically changed back to the previous state for you
	        e.target.style.cursor = 'pointer';
	    });
		myChart.Draw();
	});
}

function runBigquery() {
	var arrLabels = [];
	var arrData = [];
	
	/*var query = 'select groupTypeValue1, count(testCaseName) as num_tc ' + 
				'from motorola.com:sandbox:systemtest.TCDataXFonRegression20130129 ' +
				'group by groupTypeValue1 order by groupTypeValue1 ASC;';*/
	
	var query = 'select testResult, count(testResult) as num_result ' +
				'from [motorola.com:sandbox:systemtest.TCDataXFonRegression20130129] ' +
				'group by testResult;';
	
	$.messager.progress({
		text:'Running Google Bigquery...',
		interval:'900'
	});
	
	$.ajaxSetup({cache: false});
	$.post('src/testdashboard/runBigquery.php', {queryString:query}, function(result) {
		alert(result);
		$.messager.progress('close');
		
		try {
			var arrResult = JSON.parse(result);
			var arrRows = arrResult.rows;
			var arrFields = arrResult.fields;
		} catch (e) {
			alert(e + ': ' + result);
		}
		
		for (var i in arrRows) {
			for (var key in arrRows[i]) {
				switch (arrFields.indexOf(key)) {
				case 0:
					arrLabels.push(arrRows[i][key]);
					break;
				case 1:
					arrData.push(parseInt(arrRows[i][key]));
					break;
				}
			}
		}
		
		var myChart = new RGraph.HBar('cvs', arrData);
		
		myChart.Set('labels', arrLabels);
		myChart.Set('labels.above', true);
		myChart.Set('background.grid', false);
		myChart.Set('colors', ['#3266CC']);
		myChart.Set('events.click', function (e, shape) {
			// If you have multiple charts on your canvas the .__object__ is a reference to
	        // the last one that you created
	        var obj   = e.target.__object__;
	        var index = shape['index'];
	        var value = obj.data[index];
	        
	        alert(arrLabels[index] + ' has ' + value + ' test cases.');
		});
		myChart.Set('events.mousemove', function (e, shape) {
	        // It's automatically changed back to the previous state for you
	        e.target.style.cursor = 'pointer';
	    });
		myChart.Draw();
	});
}

function authorize() {
	$.ajaxSetup({cache: false});
	$.post('src/testdashboard/authorize.php', function(authUrl) {
		if (authUrl.length > 0) {
			$('#authLink').attr('href', authUrl);
			$('#authLink').fadeIn();
		} else {
			$('#queryButton').fadeIn();
			$('#authLink').fadeOut();
		}
	});
}