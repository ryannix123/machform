$(function(){
	
	//attach event to the forgot password checkbox
	$('#admin_forgot').bind('change', function() {
		if($(this).prop("checked") == true){
			//show reset password form
			$("#li_password,#li_remember_me").slideUp("slow",function(){
				$("#submit_button").html('<span class="icon-key"></span> Reset My Password');
			});
			
		}else{
			//show login form
			$("#li_password,#li_remember_me").slideDown("slow",function(){
				$("#submit_button").html('<span class="icon-keyhole"></span> Sign In');
			});
		}
		
	});

	//override the form submit event
	$("#form_login").submit(function(){
		if($("#admin_forgot").prop("checked") == true){
			//if forgot password submitted
			if($("#admin_username").val().trim() == ""){
				alert('Please enter your email address!');
			}else{
				var admin_email_address = $("#admin_username").val().trim();
				$("#submit_button").after("<div class='forgot_password_loader'><img src='images/loader_small_grey.gif' /></div>");
				$("#submit_button").html('<span class="icon-key"></span> Sending...');
				$("#submit_button").prop("disabled",true);
				$("#admin_forgot").prop("disabled",true);

				//do the ajax call to reset the password
				$.ajax({
					   type: "POST",
					   async: true,
					   url: "reset_password.php",
					   data: {
							  	target_email: admin_email_address
							  },
					   cache: false,
					   global: false,
					   dataType: "json",
					   error: function(xhr,text_status,e){
					   		//restore the buttons on the dialog
							$(".forgot_password_loader").remove();
							$("#submit_button").html('<span class="icon-key"></span> Reset My Password');
							$("#submit_button").prop("disabled",false);
							$("#admin_forgot").prop("disabled",false);
									
							$("#ui-dialog-title-dialog-login-page").text('Error!');
						    $("#dialog-login-page-msg").html('Unable to send new password. Error message:<br/><br/>' + xhr.responseText);
						    $("#dialog-login-page img").attr("src","images/icons/warning.png"); 
						    $("#dialog-login-page").dialog('open');
					   },
					   success: function(response_data){
							$(".forgot_password_loader").remove();
							$("#submit_button").html('<span class="icon-key"></span> Reset My Password');
							$("#submit_button").prop("disabled",false);
							$("#admin_forgot").prop("disabled",false);

							if(response_data.status == 'ok'){
							    //display the confirmation message
							    $("#ui-dialog-title-dialog-login-page").text('Reset Password Link Sent');
						    	$("#dialog-login-page-msg").text(response_data.message);
							    $("#dialog-login-page img").attr("src","images/icons/62_green_48.png");
						    }else{
						    	$("#ui-dialog-title-dialog-login-page").text('Error!');
						    	$("#dialog-login-page-msg").text(response_data.message);
						    	$("#dialog-login-page img").attr("src","images/icons/warning.png");
						    }

						    $("#dialog-login-page").dialog('open');	   
						}
				});

			}
			 
			return false;
		}else{
			return true;
		}
	});

	//Generic dialog box
	$("#dialog-login-page").dialog({
		modal: true,
		autoOpen: false,
		closeOnEscape: false,
		width: 450,
		position: ['center',175],
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

});