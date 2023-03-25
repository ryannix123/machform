$(function(){
    
	//event handler for Google Sheets toggle checkbox
	$("#toggle_integration_gsheets").click(function(){
		$("#gsheets_toggle_wrapper").append("<div class='small_loader_box' style='float: right;margin-top: -8px'><img src='images/loader_small_grey.gif' /></div>");
		
		var integration_status = 0;
		if($(this).prop("checked") == true){
			integration_status = 1;
		}else{
			integration_status = 0;
		}

		var form_id 	= $(".integrations_settings").data("formid");
		var csrf_token  = $(".integrations_settings").data("csrftoken");

		//do the ajax call to change the integration status
		$.ajax({
			   	type: "POST",
			   	async: true,
			   	url: "change_integration_status.php",
			   	data: {
			   		   form_id: form_id,
			   		   integration_status: integration_status,
			   		   csrf_token: csrf_token,
					   integration_type: 'gsheets'
					  },
			   	cache: false,
			   	global: false,
			   	dataType: "json",
			   	error: function(xhr,text_status,e){
					   //error, display the generic error message
					   $("#gsheets_toggle_wrapper .small_loader_box").remove();		  
			   },
			   	success: function(response_data){
				   $("#gsheets_toggle_wrapper .small_loader_box").remove();  
				}
		});
		
		
	});

	//event handler for Google Sheets toggle checkbox
	$("#toggle_integration_gcal").click(function(){
		$("#gcal_toggle_wrapper").append("<div class='small_loader_box' style='float: right;margin-top: -8px'><img src='images/loader_small_grey.gif' /></div>");
		
		var integration_status = 0;
		if($(this).prop("checked") == true){
			integration_status = 1;
		}else{
			integration_status = 0;
		}

		var form_id 	= $(".integrations_settings").data("formid");
		var csrf_token  = $(".integrations_settings").data("csrftoken");

		//do the ajax call to change the integration status
		$.ajax({
			   	type: "POST",
			   	async: true,
			   	url: "change_integration_status.php",
			   	data: {
			   		   form_id: form_id,
			   		   integration_status: integration_status,
			   		   csrf_token: csrf_token,
					   integration_type: 'gcal'
					  },
			   	cache: false,
			   	global: false,
			   	dataType: "json",
			   	error: function(xhr,text_status,e){
					   //error, display the generic error message
					   $("#gcal_toggle_wrapper .small_loader_box").remove();		  
			   },
			   	success: function(response_data){
				   $("#gcal_toggle_wrapper .small_loader_box").remove();  
				}
		});
		
		
	});
});