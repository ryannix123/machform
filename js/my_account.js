$(function(){
    
	/***************************************************************************************************************/	
	/* 1. Load Tooltips															   				   				   */
	/***************************************************************************************************************/
	
	//we're using tippy for the tooltip
	tippy('[data-tippy-content]',{
		trigger: 'click',
		placement: 'bottom',
		boundary: 'window',
		arrow: true
	});
	
	
	/***************************************************************************************************************/	
	/* 2. Attach event to 'Save Settings' button																   */
	/***************************************************************************************************************/
	$("#button_save_main_settings").click(function(){
		
		if($("#button_save_main_settings").text() != 'Saving...'){
				
				//display loader while saving
				$("#button_save_main_settings").prop("disabled",true);
				$("#button_save_main_settings").text('Saving...');
				$("#button_save_main_settings").after("<img style=\"margin-left: 10px\" src='images/loader_small_grey.gif' />");
				
				$("#ms_form").submit();
		}
		
		
		return false;
	});

	/***************************************************************************************************************/	
	/* 3. Dialog Box for change password																		   */
	/***************************************************************************************************************/
	
	$("#dialog-change-password").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center', 150],
		draggable: false,
		resizable: false,
		buttons: [{
			text: 'Save Password',
			id: 'dialog-change-password-btn-save-changes',
			'class': 'bb_button bb_small bb_green',
			click: function() {
				var password_1 = $.trim($("#dialog-change-password-input1").val());
				var password_2 = $.trim($("#dialog-change-password-input2").val());
				var current_user_id = $("#ms_box_account").data("userid");
				var csrf_token  	= $("#ms_box_account").data("csrftoken");

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
								  	csrf_token: csrf_token,
								  	user_id: current_user_id
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

	//Dialog to display password has been changed successfully
	$("#dialog-password-changed").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
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

	$("#ms_change_password").click(function(){
		$("#dialog-change-password").dialog('open');
		return false;
	});

	/***************************************************************************************************************/	
	/* 4. 2-Step Verification Events																			   */
	/***************************************************************************************************************/

	//attach event to 'enable 2-step verification' checkbox
	$("#tsv_enable").click(function(){
		if($(this).prop("checked") == true){
			$("#ms_box_user_tsv .ms_box_email").slideDown();
		}else{
			$("#ms_box_user_tsv .ms_box_email").slideUp();
		}
	});

	//initialize qrcode if TSV is currently disabled
	if($("#tsv_enable").prop("checked") == false){
		var qrcode = new QRCode(document.getElementById("qrcode"), { width : 140, height : 140 });
		qrcode.makeCode($("#qrcode").data('totpdata'));	
	}

	//attach event to 'Verify Code' button
	$("#button_verify_tsv").click(function(){

		if($("#button_verify_tsv").text() != 'Verifying...'){
				
				//display loader while verifying
				$("#button_verify_tsv").prop("disabled",true);
				$("#button_verify_tsv").text('Verifying...');
				$("#button_verify_tsv").after("<img style=\"margin-left: 10px\" src='images/loader_small_grey.gif' />");
				
				var csrf_token  = $("#ms_box_account").data("csrftoken");

				//verify the code
				//do the ajax call to change the password
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "my_account_verify_tsv.php",
					   data: {
							  	tsv_secret: $("#qrcode").data("tsvsecret"),
							  	tsv_code: $("#tsv_confirm_token").val(),
							  	csrf_token: csrf_token
							  },
					   cache: false,
					   global: false,
					   dataType: "json",
					   error: function(xhr,text_status,e){
						   //error, display the generic error message
						   alert('Unable to verify code!');

						   $("#button_verify_tsv").prop("disabled",false);
						   $("#button_verify_tsv").text('Verify Code');
						   $("#button_verify_tsv").next().remove();
					   },
					   success: function(response_data){	   		   	   
							if(response_data.status == 'ok'){
								//display success dialog
								$("#dialog-tsv-verified").dialog('open');
							}else{
								//display error dialog
								$("#dialog-tsv-invalid").dialog('open');
							} 
					   }
				});

				
		}

		return false;
	});

	//Dialog to display TSV successfully verified
	$("#dialog-tsv-verified").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'OK',
				id: 'dialog-tsv-verified-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$("#button_verify_tsv").prop("disabled",false);
					$("#button_verify_tsv").text('Verify Code');
					$("#button_verify_tsv").next().remove();

					$("#ms_box_user_tsv .ms_box_email").html("<h6>2-Step Verification Status &#8674; Activated</h6>")

					$(this).dialog('close');
				}
			}]

	});

	//Dialog to display TSV invalid
	$("#dialog-tsv-invalid").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 400,
		position: ['center',150],
		draggable: false,
		resizable: false,
		buttons: [{
				text: 'OK',
				id: 'dialog-tsv-invalid-btn-ok',
				'class': 'bb_button bb_small bb_green',
				click: function() {
					$("#button_verify_tsv").prop("disabled",false);
					$("#button_verify_tsv").text('Verify Code');
					$("#button_verify_tsv").next().remove();

					$(this).dialog('close');
				}
			}]

	});
	
});