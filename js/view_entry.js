$(function(){
    
	//dialog box to confirm entry deletion
	$("#dialog-confirm-entry-delete").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-confirm-entry-delete-ok").blur();
		},
		buttons: [{
				text: 'Yes. Delete this entry',
				id: 'btn-confirm-entry-delete-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					//disable the delete button while processing
					$("#btn-confirm-entry-delete-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-confirm-entry-delete-cancel").hide();
					$("#btn-confirm-entry-delete-ok").text('Deleting...');
					$("#btn-confirm-entry-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					var form_id  = $("#ve_details").data("formid");
					var entry_id = $("#ve_details").data("entryid"); 
					var selected_entry = [{name: "entry_" + entry_id, value: "1"}];
					var csrf_token  = $("#ve_details").data("csrftoken");

					//do the ajax call to delete the entries
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "delete_entries.php",
						   data: {
								  	form_id: form_id,
								  	csrf_token: csrf_token,
								  	origin: 'view_entry',
								  	incomplete_entries: $("#ve_details").data("incomplete"),
								  	selected_entries: selected_entry
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
								   if(response_data.entry_id != '0' && response_data.entry_id != ''){
								   		window.location.replace('view_entry.php?form_id=' + response_data.form_id + '&entry_id=' + response_data.entry_id);
								   }else{
								   		window.location.replace('manage_entries.php?id=' + response_data.form_id);
								   }
								  
							   }	  
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-confirm-entry-delete-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//dialog box to confirm entry status change
	$("#dialog-confirm-entry-status").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-confirm-entry-status-ok").blur();
		},
		buttons: [{
				text: 'Yes. Mark as incomplete',
				id: 'btn-confirm-entry-status-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					//disable the delete button while processing
					$("#btn-confirm-entry-status-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-confirm-entry-status-cancel").hide();
					$("#btn-confirm-entry-status-ok").text('Processing...');
					$("#btn-confirm-entry-status-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					var form_id  = $("#ve_details").data("formid");
					var entry_id = $("#ve_details").data("entryid"); 
					var csrf_token  = $("#ve_details").data("csrftoken");

					//do the ajax call to change entry status
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "change_entry_status.php",
						   data: {
								  	form_id: form_id,
								  	csrf_token: csrf_token,
								  	entry_id: entry_id
								  },
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
								   //error, display the generic error message
								   alert("Error. Unable to change status!");		  
						   },
						   success: function(response_data){
									   
							   if(response_data.status == 'ok'){
								   //display success dialog with the resume url

								   //$("#form-resume-link").text(response_data.resume_url);
							   	   $("#form-resume-link").val(response_data.resume_url);

								   $("#dialog-confirm-entry-status").dialog('close');
								   $("#dialog-entry-status-success").dialog('open');
							   }else{
							   		alert("Error. Unable to change status!");
							   }	  
									   
						   }
					});
					
				}
			},
			{
				text: 'Cancel',
				id: 'btn-confirm-entry-status-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	//open the deletion dialog when the delete entry link clicked
	$("#ve_action_delete").click(function(){	
		$("#dialog-confirm-entry-delete").dialog('open');
		return false;
	});

	//open the change status dialog when the status link clicked
	$("#ve_action_status").click(function(){	
		$("#dialog-confirm-entry-status").dialog('open');
		return false;
	});

	//dialog box to email the entry
	$("#dialog-email-entry").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',100],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Email Entry',
				id: 'dialog-email-entry-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {

					if($("#dialog-email-entry-input").val() == ""){
						alert('Please enter the email address!');
					}else{
						
						var form_id  = $("#ve_details").data("formid");
						var entry_id = $("#ve_details").data("entryid"); 
						var csrf_token  = $("#ve_details").data("csrftoken");

						//disable the email entry button while processing
						$("#dialog-email-entry-btn-ok").prop("disabled",true);
						
						//display loader image
						$("#dialog-email-entry-btn-cancel").hide();
						$("#dialog-email-entry-btn-ok").text('Sending...');
						$("#dialog-email-entry-btn-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
						
						//do the ajax call to send the entry
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "email_entry.php",
							   data: {
									  	form_id: form_id,
									  	entry_id: entry_id,
									  	csrf_token: csrf_token,
									  	email_template: $("#dialog-email-entry-template").val(),
									  	target_email: $("#dialog-email-entry-input").val(),
									  	email_note: $("#dialog-email-entry-note").val()
									  },
							   cache: false,
							   global: false,
							   dataType: "json",
							   error: function(xhr,text_status,e){
							   		//restore the buttons on the dialog
									$("#dialog-email-entry").dialog('close');
									$("#dialog-email-entry-btn-ok").prop("disabled",false);
									$("#dialog-email-entry-btn-cancel").show();
									$("#dialog-email-entry-btn-ok").text('Email Entry');
									$("#dialog-email-entry-btn-ok").next().remove();
									$("#dialog-email-entry-input").val('');
									
									alert('Error! Unable to send entry. \nError message: ' + xhr.responseText); 
							   },
							   success: function(response_data){
									
									//restore the buttons on the dialog
									$("#dialog-email-entry").dialog('close');
									$("#dialog-email-entry-btn-ok").prop("disabled",false);
									$("#dialog-email-entry-btn-cancel").show();
									$("#dialog-email-entry-btn-ok").text('Email Entry');
									$("#dialog-email-entry-btn-ok").next().remove();
									$("#dialog-email-entry-input").val('');
									   	   
								   if(response_data.status == 'ok'){
									   //display the confirmation message
									   $("#dialog-entry-sent").dialog('open');
								   } 
									   
							   }
						});
					}
				}
			},
			{
				text: 'Cancel',
				id: 'dialog-email-entry-btn-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//dialog box to decrypt the entry
	$("#dialog-decrypt-entry").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',100],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'Submit',
				id: 'dialog-decrypt-entry-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {

					if($("#dialog-decrypt-entry-input").val() == ""){
						alert('Please enter the Private Key!');
					}else{
						
						var form_id  = $("#ve_details").data("formid");
						var entry_id = $("#ve_details").data("entryid"); 
						var csrf_token = $("#ve_details").data("csrftoken");

						//disable the email entry button while processing
						$("#dialog-decrypt-entry-btn-ok").prop("disabled",true);
						
						//display loader image
						$("#dialog-decrypt-entry-btn-cancel").hide();
						$("#dialog-decrypt-entry-btn-ok").text('Processing...');
						$("#dialog-decrypt-entry-btn-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
						
						//do the ajax call to send the entry
						$.ajax({
							   type: "POST",
							   async: true,
							   url: "encryption_set_privatekey.php",
							   data: {
									  	form_id: form_id,
									  	csrf_token: csrf_token,
									  	private_key: $("#dialog-decrypt-entry-input").val()
									  },
							   cache: false,
							   global: false,
							   dataType: "json",
							   error: function(xhr,text_status,e){
							   		//restore the buttons on the dialog
									$("#dialog-decrypt-entry").dialog('close');
									$("#dialog-decrypt-entry-btn-ok").prop("disabled",false);
									$("#dialog-decrypt-entry-btn-cancel").show();
									$("#dialog-decrypt-entry-btn-ok").text('Submit');
									$("#dialog-decrypt-entry-btn-ok").next().remove();
									$("#dialog-decrypt-entry-input").val('');
									
									alert('Error! Unable to send private key. \nError message: ' + xhr.responseText); 
							   },
							   success: function(response_data){
												   	   
								   if(response_data.status == 'ok'){
									   //reload the page
									   window.location.replace('view_entry.php?form_id=' + form_id + '&entry_id=' + entry_id);
								   } 
									   
							   }
						});
					}
				}
			},
			{
				text: 'Cancel',
				id: 'dialog-decrypt-entry-btn-cancel',
				'class': 'btn_secondary_action',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
	$("#button_decrypt_entry_data").click(function(){
		$("#dialog-decrypt-entry").dialog('open');
		return false;
	});

	//if the user submit the form by hitting the enter key, make sure to call the button-decrypt-entry handler
	$("#dialog-decrypt-entry-form").submit(function(){
		$("#dialog-decrypt-entry-btn-ok").click();
		return false;
	});

	//open the email entry dialog when the email entry link clicked
	$("#ve_action_email").click(function(){	
		$("#dialog-email-entry").dialog('open');
		return false;
	});

	//if the user submit the form by hitting the enter key, make sure to call the button-email-entry handler
	$("#dialog-email-entry-form").submit(function(){
		$("#dialog-email-entry-btn-ok").click();
		return false;
	});

	//Dialog to display entry being sent successfully
	$("#dialog-entry-sent").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',100],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'OK',
				id: 'dialog-entry-sent-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});

	//Dialog to display entry status being changed successfully
	$("#dialog-entry-status-success").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'OK',
				id: 'dialog-entry-status-success-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$(this).dialog('close');

					var form_id  = $("#ve_details").data("formid");
					var entry_id = $("#ve_details").data("entryid"); 

					window.location.replace('view_entry.php?form_id=' + form_id + '&entry_id=' + entry_id);
				}
			}]

	});

	//attach event to "change payment status" link
	$("#payment_status_change_link").click(function(){	
		$("#payment_status_static").hide();
		$("#payment_status_form").show();

		return false;
	});

	//attach event to "cancel" link on payment status
	$("#payment_status_cancel_link").click(function(){	
		$("#payment_status_form").hide();
		$("#payment_status_static").show();

		return false;
	});

	//attach event to "save" link on payment status
	$("#payment_status_save_link").click(function(){	
		
		$("#payment_status_dropdown").prop("disabled",true);
		$("#payment_status_save_cancel").hide();
		$("#payment_status_loader").show();

		var form_id  = $("#ve_details").data("formid");
		var entry_id = $("#ve_details").data("entryid");
		var csrf_token  = $("#ve_details").data("csrftoken");

		//do the ajax call to send the entry
		$.ajax({
			   type: "POST",
			   async: true,
			   url: "change_payment_status.php",
			   data: {
					  	form_id: form_id,
					  	entry_id: entry_id,
					  	csrf_token: csrf_token,
					  	payment_status: $("#payment_status_dropdown").val()
					  },
			   cache: false,
			   global: false,
			   dataType: "json",
			   error: function(xhr,text_status,e){
			   		//restore the links to original and display alert
					$("#payment_status_dropdown").prop("disabled",false);
					$("#payment_status_save_cancel").show();
					$("#payment_status_loader").hide();

					alert('Error! Unable to change status. \nError message: ' + xhr.responseText); 
			   },
			   success: function(response_data){
					//restore the link and update the payment status
					$("#payment_status_dropdown").prop("disabled",false);
					$("#payment_status_save_cancel").show();
					$("#payment_status_loader").hide();

					if(response_data.status == 'ok'){
						$(".payment_status").removeClass('paid').text(response_data.payment_status.toUpperCase());	

						if(response_data.payment_status == 'paid'){
							$(".payment_status").addClass('paid');
						}

						$("#payment_status_form").hide();
						$("#payment_status_static").show();
					}else{
						alert('Error! Unable to change status. \nError message: ' + xhr.responseText); 
					}    
			   }
		});

		return false;
	});

	//Attach event to "more options" 
	$("#more_option_email_entry").click(function(){
		if($(this).text() == 'more options'){
			//expand more options
			$("#ve_box_email_more").slideDown();
			$(this).text('hide options');
		}else{
			$("#ve_box_email_more").slideUp();
			$(this).text('more options');
		}

		return false;
	});

	//Attach event "..." recent emails button
	$("#toggle-recent-emails").click(function(){

		if($(this).text() == '>'){
			$("#email-entry-suggestion-full").slideDown();
			$(this).text('x');
		}else{
			$("#email-entry-suggestion-full").slideUp();
			$(this).text('>');
		}

		return false;
	});

	$(".email-entry-suggestion-link:not(.email-entry-suggestion-toggle)").click(function(){
		if($("#dialog-email-entry-input").val().length > 0){
			$("#dialog-email-entry-input").val($("#dialog-email-entry-input").val() + ',' + $(this).text());
		}else{
			$("#dialog-email-entry-input").val($(this).text());
		}
		
		$(this).remove();

		if($(".email-entry-suggestion-link:not(.email-entry-suggestion-toggle)").length == 0){
			$(".email-entry-suggestion-link,.email-entry-suggestion-toggle,#infomessage_recent_emails").remove();
		}

		if($("#email-entry-suggestion-full .email-entry-suggestion-link").length == 0){
			$(".email-entry-suggestion-toggle").remove();
		}

		return false;
	});

	//Attach event to approval buttons (approve/deny)
	$("#button_approval_approve,#button_approval_deny").click(function(){
		var selected_button = $(this).attr("id");
		var approval_state  = '';

		if($(this).text() == 'Processing...'){
			return false;
		}

		var form_id  = $("#ve_details").data("formid");
		var entry_id = $("#ve_details").data("entryid");
		var csrf_token = $("#ve_details").data("csrftoken"); 

		if(selected_button == 'button_approval_approve'){
			approval_state = 'approved';
			
			$("#button_approval_approve").text("Processing...");
			$("#button_approval_deny").hide();
		}else if(selected_button == 'button_approval_deny'){
			approval_state = 'denied';

			$("#button_approval_deny").text("Processing...");
			$("#button_approval_approve").hide();
		}

		//hide the approval buttons while processing
		$("#button_approval_deny").after("<div class='small_loader_box' style=\"float: none; display: inline;padding: 0; margin-left: 5px;\"><img src='images/loader_small_grey.gif' /></div>");

		//do the ajax call to approve the entry
		$.ajax({
			   type: "POST",
			   async: true,
			   url: "approve_deny_entry.php",
			   data: {
					  	form_id: form_id,
					  	entry_id: entry_id,
					  	csrf_token: csrf_token,
					  	approval_state: approval_state,
					  	approval_note: $("#ve-approval-add-note").val(),
					  	show_message: '1'
					  },
			   cache: false,
			   global: false,
			   dataType: "json",
			   error: function(xhr,text_status,e){
					alert('Error! Unable to approve entry. \nError message: ' + xhr.responseText); 
			   },
			   success: function(response_data){
					//reload current page
					if(response_data.status == 'ok'){
						window.location.replace('view_entry.php?form_id=' + response_data.form_id + '&entry_id=' + response_data.entry_id);
				   	}else{
					   alert('Error! Unable to save approve entry. Please try again.');
				   	}
			   }
		});

		return false;
	});

	//copy link to clipboard event handler
	var clipboard = new ClipboardJS('.trigger-edit-resume-link');
    clipboard.on('success', function(e) {
        //display notifications on success
		Swal.fire({
		  toast: true,
		  position: 'center',
		  type: 'success',
		  title: 'Link copied.',
		  showConfirmButton: false,
		  timer: 2000
		});
    });

	
});