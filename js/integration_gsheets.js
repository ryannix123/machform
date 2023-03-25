$(function(){
    
	//we're using tippy for the tooltip
	tippy('[data-tippy-content]',{
		trigger: 'click',
		placement: 'bottom',
		boundary: 'window',
		arrow: true
	});

	//'save integration' button being clicked
	$("#button_save_integration").click(function(){
		
		//display loader while saving
		if($("#button_save_integration").text() == 'Saving...'){
			return false;
		}

		$("#button_save_integration").text('Saving...');
		$("#button_save_integration").after("<div class='small_loader_box' style='float: left;margin-top: -8px'><img src='images/loader_small_grey.gif' /></div>");
		$("#button_remove_integration").hide();

		var form_id = $(".integrations_settings").data("formid");
		var csrf_token = $(".integrations_settings").data("csrftoken");
		var selected_columns = $("#gsheets_field_selection_list input.checkbox:checked").serializeArray();

		var gsheet_delay_notification_until_paid = 0;
		if($("#gsheet_delay_notification_until_paid").prop("checked") == true){
			gsheet_delay_notification_until_paid = 1;
		}

		var gsheet_delay_notification_until_approved = 0;
		if($("#gsheet_delay_notification_until_approved").prop("checked") == true){
			gsheet_delay_notification_until_approved = 1;
		}
		
		//send to backend using ajax call
		$.ajax({
			   	type: "POST",
			   	async: true,
			   	url: "save_integration_gsheets.php",
			   	data: {
			   		   form_id: form_id,
			   		   csrf_token: csrf_token,
			   		   gsheet_delay_notification_until_paid: gsheet_delay_notification_until_paid,
			   		   gsheet_delay_notification_until_approved: gsheet_delay_notification_until_approved,
					   col_pref: selected_columns
					  },
			   	cache: false,
			   	global: false,
			   	dataType: "json",
			   	error: function(xhr,text_status,e){
					   //error, display the generic error message		  
			   },
			   	success: function(response_data){
					   
				   if(response_data.status == 'ok'){
					   window.location.replace('integration_settings.php?id=' + response_data.form_id);
				   }	  
				}
		});

		return false;
	});

	//dialog box to confirm integration deletion
	$("#dialog-confirm-integration-delete").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-confirm-integration-delete-ok").blur();
		},
		buttons: [{
				text: 'Yes. Remove Integration',
				id: 'btn-confirm-integration-delete-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					//disable the delete button while processing
					$("#btn-confirm-integration-delete-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-confirm-integration-delete-cancel").hide();
					$("#btn-confirm-integration-delete-ok").text('Removing...');
					$("#btn-confirm-integration-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					var form_id = $(".integrations_settings").data("formid");
					var csrf_token = $(".integrations_settings").data("csrftoken");

					//do the ajax call to remove the integration
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "delete_integration_gsheets.php",
						   data: {
								  	form_id: form_id,
								  	csrf_token: csrf_token
								  },
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
								   //error, display the generic error message		  
						   },
						   success: function(response_data){
									   
							   if(response_data.status == 'ok'){
								   //redirect to entries page again
								   window.location.replace('integration_settings.php?id=' + response_data.form_id);
							   }	  
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-confirm-integration-delete-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//open the dialog when the 'remove integration' link clicked
	$("#button_remove_integration").click(function(){
		$("#dialog-confirm-integration-delete").dialog('open');
		return false;
	});
});