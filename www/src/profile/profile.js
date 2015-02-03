

$(window).resize(function() {
	resizeBody();
	// Resize the panel to the size of body.
	$("#adminPanel").panel({
		width:$("body").width(),
		height:$("body").height(),
	});
});

function resizeBody() {
    $("body").width($(window).width() - 15);
    $("body").height($(window).height() - 80);
}

/*
 * Document Ready
 * All javascript will be here
 */
$(document).ready(function() {

    	resizeBody();
	$("#adminPanel").panel({
		fit:true,
                width:$("body").width(),
                height:$("body").height(),
        });
});
    
function save_userProp(){
        var curr_rows = $('#userProp').propertygrid('getRows');
        $.messager.progress({
                title:'Please waiting',
                text:'Processing...',
                interval:'600'
        });


        $.post('save_userProp.php',{username:username,curr_rows:curr_rows}, function(result){
                $.messager.progress('close');
                alert("Data is saved!");
        },'json');
}

function cancel_userProp(){
        $('#userProp').datagrid('reload');

}

function openJira(){
        alert("Please file an issue under Component = InvaderPlus");
        window.open("http://idart.mot.com/secure/CreateIssue.jspa?pid=10023&issuetype=5", "target");
}


function openlink(url){
        $.messager.progress({
                title:'Please waiting',
                text:'Processing...',
                interval:'600'
        });
        $.post('src/profile/set_userProp.php',{username:username}, function(result){
                $.messager.progress('close');
		if(result.msg == "no default json file"){
			alert("no default json file");
			exit;
		}
        	$('#setlist').attr('src',url);
        },'json');

}

