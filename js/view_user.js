$(function(){
    
	//dialog box to confirm user deletion
	$("#dialog-confirm-user-delete").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 550,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		open: function(){
			$("#btn-confirm-user-delete-ok").blur();
		},
		buttons: [{
				text: 'Yes. Delete this user',
				id: 'btn-confirm-user-delete-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					
					//disable the delete button while processing
					$("#btn-confirm-user-delete-ok").prop("disabled",true);
						
					//display loader image
					$("#btn-confirm-user-delete-cancel").hide();
					$("#btn-confirm-user-delete-ok").text('Deleting...');
					$("#btn-confirm-user-delete-ok").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");
					
					var user_id  	 = $("#vu_details").data("userid");
					var csrf_token   = $("#vu_details").data("csrftoken");
					var current_user = [{ name : "entry_" + user_id, value : "1"}];
					
					//do the ajax call to delete the users
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "change_user_status.php",
						   data: {
								  	action: 'delete',
								  	origin: 'view_user',
								  	csrf_token: csrf_token,
								  	selected_users: current_user
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
								   if(response_data.user_id != '0' && response_data.user_id != ''){
								   		window.location.replace('view_user.php?id=' + response_data.user_id);
								   }else{
								   		window.location.replace('manage_users.php');
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
	
	//open the deletion dialog when the delete user link clicked
	$("#vu_action_delete").click(function(){	
		$("#dialog-confirm-user-delete").dialog('open');
		return false;
	});

	//suspend/unspend user when the suspend link being clicked
	$("#vu_action_suspend").click(function(){

		if($(this).data('processing') !== true){
			$(this).data('processing',true);

			if($(this).hasClass('unsuspend')){
				
				//unsuspend the user
				$(this).removeClass('unsuspend').append(' <img src="images/loader_small_grey.gif" style="vertical-align: middle" />');

				//do the ajax call to unsuspend the user
				var user_id  	 = $("#vu_details").data("userid");
				var csrf_token   = $("#vu_details").data("csrftoken");
				var current_user = [{ name : "entry_" + user_id, value : "1"}];
					
				//do the ajax call to delete the users
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "change_user_status.php",
					   data: {
							  	action: 'unsuspend',
							  	csrf_token: csrf_token,
							  	selected_users: current_user
							  },
					   cache: false,
					   global: false,
					   dataType: "json",
					   error: function(xhr,text_status,e){
							   //error, display the generic error message		  
					   },
					   success: function(response_data){
									   
						   if(response_data.status == 'ok'){
						   		 $("#vu_action_suspend").data("processing",false);
							     $("#vu_action_suspend").html('<span class="icon-user-block"></span>Suspend');
							     $("#vu_suspended").fadeOut(function(){
							     	$(this).remove();
							     });
						   }	  
								   
					   }
				});
			}else{
				//suspend the user
				$(this).addClass('unsuspend').append(' <img src="images/loader_small_grey.gif" style="vertical-align: middle" />');

				//do the ajax call to unsuspend the user
				var user_id  	 = $("#vu_details").data("userid");
				var csrf_token   = $("#vu_details").data("csrftoken");
				var current_user = [{ name : "entry_" + user_id, value : "1"}];
					
				//do the ajax call to delete the users
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "change_user_status.php",
					   data: {
							  	action: 'suspend',
							  	no_session_msg: '1',
							  	csrf_token: csrf_token,
							  	selected_users: current_user
							  },
					   cache: false,
					   global: false,
					   dataType: "json",
					   error: function(xhr,text_status,e){
							   //error, display the generic error message		  
					   },
					   success: function(response_data){
									   
						   if(response_data.status == 'ok'){
						   		 $("#vu_action_suspend").data("processing",false);
							     $("#vu_action_suspend").html('<span class="icon-unlocked"></span>Unblock');
							     $("#vu_profile").append('<div id="vu_suspended" style="display: none">This user is currently being <span>SUSPENDED</span></div>');
						   		 $("#vu_suspended").fadeIn();
						   }	  
								   
					   }
				});
			}
		}

		return false;
	});
	

	//dialog box to change password
	$("#dialog-change-password").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		buttons: [{
			text: 'Save Password',
			id: 'dialog-change-password-btn-save-changes',
			'class': 'bb_button bb_small bb_green',
			click: function() {
				var password_1 = $.trim($("#dialog-change-password-input1").val());
				var password_2 = $.trim($("#dialog-change-password-input2").val());
				var current_user_id = $("#vu_details").data("userid");
				var csrf_token  	= $("#vu_details").data("csrftoken");

				var send_login_info = 0;
				if($("#dialog-change-password-send-login").prop("checked") == true){
					send_login_info = 1;
				}

				if(password_1 == "" || password_2 == ""){
					alert('Please enter both password fields!');
				}else if(password_1 != password_2){
					alert("Please enter the same password for both fields!");
				}else{
					//disable the save changes button while processing
					$("#dialog-change-password-btn-save-changes").prop("disabled",true);
						
					//display loader image
					$("#dialog-change-password-btn-cancel").hide();
					$("#dialog-change-password-btn-save-changes").text('Saving...');
					$("#dialog-change-password-btn-save-changes").after("<div class='small_loader_box'><img src='images/loader_small_grey.gif' /></div>");

					//do the ajax call to change the password
					$.ajax({
						   type: "POST",
						   async: true,
						   url: "change_password.php",
						   data: {
								  	np: password_1,
								  	user_id: current_user_id,
								  	csrf_token: csrf_token,
								  	send_login: send_login_info
								  },
						   cache: false,
						   global: false,
						   dataType: "json",
						   error: function(xhr,text_status,e){
							   //error, display the generic error message
							   alert('Unable to save the password!');
							   $(this).dialog('close');	  
						   },
						   success: function(response_data){	   
							   //restore the buttons on the dialog
								$("#dialog-change-password").dialog('close');
								$("#dialog-change-password-btn-save-changes").prop("disabled",false);
								$("#dialog-change-password-btn-cancel").show();
								$("#dialog-change-password-btn-save-changes").text('Save Password');
								$("#dialog-change-password-btn-save-changes").next().remove();
								$("#dialog-change-password-input1").val('');
								$("#dialog-change-password-input2").val('');
								$("#dialog-change-password-send-login").prop("checked",false);
									   	   
								if(response_data.status == 'ok'){
									//display the confirmation message
									$("#dialog-password-changed").dialog('open');
								} 
						   }
					});
				}
			}
		},
		{
			text: 'Cancel',
			id: 'dialog-change-password-btn-cancel',
			'class': 'btn_secondary_action',
			click: function() {
				$(this).dialog('close');
			}
		}]

	});

	//open the change password dialog
	$("#vu_action_password").click(function(){	
		$("#dialog-change-password").dialog('open');
		return false;
	});

	//Dialog to display password has been changed successfully
	$("#dialog-password-changed").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center','center'],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'OK',
				id: 'dialog-password-changed-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$(this).dialog('close');
				}
			}]

	});
	
});